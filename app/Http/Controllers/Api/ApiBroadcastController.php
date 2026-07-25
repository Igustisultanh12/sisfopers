<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Broadcast;
use App\Models\BroadcastResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiBroadcastController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $personel = $user->personel;

        if (!$personel) {
            return response()->json([
                'success' => false,
                'message' => 'Data personel Anda tidak ditemukan.'
            ], 404);
        }

        $broadcasts = Broadcast::where(function ($query) use ($personel) {
                $query->whereHas('targets', function($q) use ($personel) {
                    $q->where('personel_id', $personel->id);
                })
                ->orWhere('matra', $personel->matra)
                ->orWhere('matra', 'ALL');
            })
            ->latest()
            ->paginate(15);

        $transformed = collect($broadcasts->items())->map(function ($broadcast) use ($personel) {
            // Cek status respon presensi untuk broadcast ini
            $response = BroadcastResponse::where('broadcast_id', $broadcast->id)
                ->where('personel_id', $personel->id)
                ->first();

            return [
                'id' => $broadcast->id,
                'uuid' => $broadcast->uuid,
                'title' => $broadcast->title,
                'description' => $broadcast->description,
                'matra' => $broadcast->matra,
                'event_date' => $broadcast->event_date ? $broadcast->event_date->toDateString() : null,
                'created_at' => $broadcast->created_at->toDateTimeString(),
                'response' => $response ? [
                    'status' => $response->status_attendance ?? $response->status,
                    'notes' => $response->notes,
                    'permit_letter' => $response->permit_letter,
                    'responded_at' => $response->responded_at,
                ] : null
            ];
        });

        return response()->json([
            'success' => true,
            'broadcasts' => [
                'current_page' => $broadcasts->currentPage(),
                'data' => $transformed,
                'last_page' => $broadcasts->lastPage(),
                'total' => $broadcasts->total()
            ]
        ]);
    }

    public function respond(Request $request, $uuid)
    {
        $request->validate([
            'status' => 'required|in:HADIR,IZIN,ABSEN,TIDAK_HADIR',
            'notes'  => 'nullable|string|max:255',
            'permit_letter' => 'nullable|boolean'
        ]);

        $user = $request->user();
        $personel = $user->personel;
        $broadcast = Broadcast::where('uuid', $uuid)->firstOrFail();

        if (!$personel) {
            return response()->json([
                'success' => false,
                'message' => 'Data personel Anda tidak ditemukan.'
            ], 404);
        }

        $response = BroadcastResponse::updateOrCreate(
            [
                'broadcast_id' => $broadcast->id,
                'personel_id'  => $personel->id,
            ],
            [
                'status_attendance' => $request->status, // Mengisi kolom status_attendance
                'notes'             => $request->notes,
                'permit_letter'     => filter_var($request->permit_letter, FILTER_VALIDATE_BOOLEAN) ? 'YA' : 'TIDAK',
                'responded_at'      => now(),
                'created_at'        => now()
            ]
        );

        // Tambah Notifikasi Lonceng
        $user->notify(new \App\Notifications\SystemNotification(
            'Presensi Berhasil Terkirim',
            "Anda berhasil mengirimkan konfirmasi presensi untuk kegiatan: {$broadcast->title}.",
            'success',
            route('personel.broadcast.show', $broadcast->uuid)
        ));

        // Kirim notifikasi WA ke seluruh Admin & Koordinator (seperti di BroadcastResponseController)
        $adminsAndCoordinators = \App\Models\User::whereHas('role', fn($q) => $q->whereIn('name', ['admin', 'kordinator_angkatan', 'kordinator_matra']))
            ->with('personel')
            ->get();
            
        foreach ($adminsAndCoordinators as $recipientUser) {
            if ($recipientUser->personel && $recipientUser->personel->phone_number) {
                if ($recipientUser->personel->id === $personel->id) {
                    continue;
                }
                
                $msgAdmin = "🔔 *RESPON KEHADIRAN BARU (MOBILE APP)*\n\n"
                    . "Kegiatan: {$broadcast->title}\n"
                    . "Personel: {$personel->full_name} ({$personel->pangkat})\n"
                    . "Status: {$request->status}\n"
                    . "Catatan: " . ($request->notes ?? '-') . "\n"
                    . "Minta Izin: " . (filter_var($request->permit_letter, FILTER_VALIDATE_BOOLEAN) ? 'YA' : 'TIDAK') . "\n\n"
                    . "Tinjau rekapitulasi di platform SISFOPERS.";
                \App\Services\WhatsappService::sendMessage($recipientUser->personel->phone_number, $msgAdmin);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Konfirmasi lembar kehadiran Anda berhasil dikirimkan.',
            'response' => [
                'status' => $response->status_attendance,
                'notes' => $response->notes,
                'permit_letter' => $response->permit_letter,
                'responded_at' => $response->responded_at,
            ]
        ]);
    }
}
