<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\Broadcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiDashboardController extends Controller
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

        $totalKegiatan = $personel->broadcastResponses()->count();
        $totalHadir    = $personel->broadcastResponses()->where('status', 'HADIR')->count();
        $totalIzin     = $personel->broadcastResponses()->where('status', 'IZIN')->count();

        $latestBroadcasts = Broadcast::where(function ($query) use ($personel) {
                $query->whereHas('targets', function($q) use ($personel) {
                    $q->where('personel_id', $personel->id);
                })
                ->orWhere('matra', $personel->matra)
                ->orWhere('matra', 'ALL');
            })
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($broadcast) {
                return [
                    'id' => $broadcast->id,
                    'uuid' => $broadcast->uuid,
                    'title' => $broadcast->title,
                    'description' => $broadcast->description,
                    'matra' => $broadcast->matra,
                    'event_date' => $broadcast->event_date ? $broadcast->event_date->toDateString() : null,
                    'created_at' => $broadcast->created_at->toDateTimeString(),
                ];
            });

        return response()->json([
            'success' => true,
            'stats' => [
                'total_kegiatan' => $totalKegiatan,
                'total_hadir'    => $totalHadir,
                'total_izin'     => $totalIzin,
            ],
            'latest_broadcasts' => $latestBroadcasts
        ]);
    }
}
