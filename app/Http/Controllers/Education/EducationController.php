<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Models\MasterKepangkatan;
use App\Models\Personel;
use App\Models\RiwayatPendidikan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class EducationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $personel = $user->personel;
        
        if (!$personel) {
            $redirectRoute = $user->hasRole('admin') ? 'admin.dashboard' : 'personel.dashboard';
            return redirect()->route($redirectRoute)->with('error', 'Profil personel Anda belum terkonfigurasi. Hubungi administrator untuk mendaftarkan data personel Anda.');
        }

        $personel->ensureKomcadEducationExists();

        $personel->load('riwayatPendidikan.verifier');

        return Inertia::render('Personel/Education/Index', [
            'personel' => $personel,
            'items' => $personel->riwayatPendidikan()
                ->with('verifier')
                ->latest()
                ->get(),
            'options' => $this->options(),
            'routes' => [
                'store' => 'personel.education.store',
                'update' => 'personel.education.update',
                'destroy' => 'personel.education.destroy',
                'verify' => null,
                'params' => [],
            ],
        ]);
    }

    public function adminIndex(string $uuid)
    {
        $personel = Personel::with('riwayatPendidikan.verifier')->where('uuid', $uuid)->firstOrFail();

        $personel->ensureKomcadEducationExists();

        return Inertia::render('Personel/Education/Index', [
            'personel' => $personel,
            'items' => $personel->riwayatPendidikan()
                ->with('verifier')
                ->latest()
                ->get(),
            'options' => $this->options(),
            'routes' => [
                'store' => 'admin.personel.education.store',
                'update' => 'admin.education.update',
                'destroy' => 'admin.education.destroy',
                'verify' => 'admin.education.verify',
                'params' => ['uuid' => $personel->uuid],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $personel = $user->personel;
        if (!$personel) {
            return back()->with('error', 'Profil personel tidak ditemukan.');
        }

        $validated = $this->validatePayload($request);

        foreach (['file_ijazah_path' => 'file_ijazah', 'file_sertifikat_path' => 'file_sertifikat'] as $column => $field) {
            if ($request->hasFile($field)) {
                try {
                    $validated[$column] = $request->file($field)->store('personel/pendidikan', 'private');
                } catch (\Exception $e) {
                    $validated[$column] = $request->file($field)->store('personel/pendidikan', 'local');
                }
            }
        }

        $personel->riwayatPendidikan()->create(array_merge($validated, [
            'ocr_status' => 'IDLE',
        ]));

        return back()->with('success', 'Riwayat pendidikan berhasil ditambahkan.');
    }

    public function adminStore(Request $request, string $uuid)
    {
        $personel = Personel::where('uuid', $uuid)->firstOrFail();
        $validated = $this->validatePayload($request);

        foreach (['file_ijazah_path' => 'file_ijazah', 'file_sertifikat_path' => 'file_sertifikat'] as $column => $field) {
            if ($request->hasFile($field)) {
                try {
                    $validated[$column] = $request->file($field)->store('personel/pendidikan', 'private');
                } catch (\Exception $e) {
                    $validated[$column] = $request->file($field)->store('personel/pendidikan', 'local');
                }
            }
        }

        $personel->riwayatPendidikan()->create(array_merge($validated, [
            'ocr_status' => 'IDLE',
        ]));

        return back()->with('success', 'Riwayat pendidikan personel berhasil ditambahkan.');
    }

    /**
     * Halaman admin — daftar semua riwayat pendidikan yang belum diverifikasi
     */
    public function adminVerifList(Request $request)
    {
        $query = Personel::with(['riwayatPendidikan' => function ($q) {
            $q->whereNull('verified_at')->with('verifier');
        }])
        ->whereHas('riwayatPendidikan', function ($q) {
            $q->whereNull('verified_at');
        });

        // Filter pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nikc', 'like', "%{$search}%")
                  ->orWhereHas('riwayatPendidikan', function ($sub) use ($search) {
                      $sub->where('nama_institusi', 'like', "%{$search}%")
                          ->orWhere('program_studi', 'like', "%{$search}%");
                  });
            });
        }

        // Filter jenis
        if ($request->filled('jenis')) {
            $jenis = $request->jenis;
            $query->whereHas('riwayatPendidikan', function ($q) use ($jenis) {
                $q->whereNull('verified_at')->where('jenis', $jenis);
            })
            ->with(['riwayatPendidikan' => function ($q) use ($jenis) {
                $q->whereNull('verified_at')->where('jenis', $jenis)->with('verifier');
            }]);
        }

        $items = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/VerifikasiPendidikan/Index', [
            'items'   => $items,
            'filters' => $request->only(['search', 'jenis']),
        ]);
    }

    public function update(Request $request, int $education)
    {
        $item = RiwayatPendidikan::findOrFail($education);
        $this->authorizeManage($request, $item);

        $validated = $this->validatePayload($request, isUpdate: true);

        foreach (['file_ijazah_path' => 'file_ijazah', 'file_sertifikat_path' => 'file_sertifikat'] as $column => $field) {
            if ($request->hasFile($field)) {
                if ($item->{$column}) {
                    Storage::disk('private')->delete($item->{$column});
                }
                $validated[$column] = $request->file($field)->store('personel/pendidikan', 'private');
            }
        }

        $item->update(array_merge($validated, [
            'verified_at' => null,
            'verified_by' => null,
            'ocr_status' => 'IDLE',
        ]));

        return back()->with('success', 'Riwayat pendidikan berhasil diperbarui dan menunggu verifikasi ulang.');
    }

    public function destroy(Request $request, int $education)
    {
        $item = RiwayatPendidikan::findOrFail($education);
        $this->authorizeManage($request, $item);
        $item->delete();

        return back()->with('success', 'Riwayat pendidikan berhasil dihapus.');
    }

    public function verify(Request $request, int $education)
    {
        $user = $request->user();
        $item = RiwayatPendidikan::with('personel.user')->findOrFail($education);

        abort_unless(
            $user->hasRole('admin')
            || $this->userMengkoordinasi($user, $item->personel_id),
            403
        );

        $item->update([
            'verified_at' => now(),
            'verified_by' => $user->id,
        ]);

        $personel = $item->personel;
        if ($personel && $personel->user) {
            $personel->user->notify(new \App\Notifications\SystemNotification(
                'Verifikasi Pendidikan Disetujui',
                "Riwayat pendidikan ({$item->jenis} - {$item->nama_institusi}) Anda telah diverifikasi dan DISETUJUI.",
                'education',
                route('personel.education.index')
            ));
        }

        return back()->with('success', 'Riwayat pendidikan berhasil diverifikasi.');
    }

    public function reject(Request $request, int $education)
    {
        $user = $request->user();
        $item = RiwayatPendidikan::with('personel.user')->findOrFail($education);

        abort_unless(
            $user->hasRole('admin')
            || $this->userMengkoordinasi($user, $item->personel_id),
            403
        );

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $personel = $item->personel;
        if ($personel && $personel->user) {
            $personel->user->notify(new \App\Notifications\SystemNotification(
                'Verifikasi Pendidikan Ditolak',
                "Pengajuan riwayat pendidikan ({$item->jenis} - {$item->nama_institusi}) Anda DITOLAK dengan alasan: \"{$request->reason}\". Silakan unggah kembali berkas yang valid.",
                'education',
                route('personel.education.index')
            ));
        }

        // Hapus file fisik ijazah/sertifikat jika ada
        if ($item->file_ijazah_path) {
            try {
                Storage::disk('private')->delete($item->file_ijazah_path);
            } catch (\Exception $e) {}
        }
        if ($item->file_sertifikat_path) {
            try {
                Storage::disk('private')->delete($item->file_sertifikat_path);
            } catch (\Exception $e) {}
        }

        // Hapus permanen agar personel dapat menginput ulang data baru
        $item->forceDelete();

        return back()->with('success', 'Riwayat pendidikan berhasil ditolak dan notifikasi telah dikirimkan ke personel.');
    }

    private function userMengkoordinasi(?\Illuminate\Contracts\Auth\Authenticatable $user, int $personelId): bool
    {
        if (!$user) {
            return false;
        }
        return $user->hasRole('koordinator') || $user->hasRole('wakil koordinator');
    }

    private function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $jenis = $request->input('jenis', 'AKADEMIK');
        
        $validJenjangs = ['SD', 'SMP', 'SMA', 'D3', 'D4', 'S1', 'S2', 'S3', 'PROFESI', 'LAIN'];
        if ($jenis === 'MILITER') {
            $validJenjangs = [
                'Letnan Dua Perwira Komcad',
                'Sersan Dua Bintara Komcad',
                'Prajurit dua Tamtama Komcad',
                'Lain'
            ];
        } elseif ($jenis === 'DIKLAT') {
            $validJenjangs = ['Sertifikasi', 'Kursus', 'Pelatihan', 'Penataran', 'Lain'];
        }

        return $request->validate([
            'jenis' => ['required', Rule::in(['AKADEMIK', 'DIKLAT', 'MILITER'])],
            'jenjang' => ['required', Rule::in($validJenjangs)],
            'program_studi' => ['nullable', 'string', 'max:150'],
            'nama_institusi' => ['nullable', 'string', 'max:150'],
            'tahun_lulus' => ['nullable', 'integer', 'min:1945', 'max:' . ((int) date('Y') + 1)],
            'nomor_ijazah' => ['nullable', 'string', 'max:100'],
            'front_title' => ['nullable', 'string', 'max:30'],
            'suffix_gelar' => ['nullable', 'string', 'max:100'],
            'file_ijazah' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'file_sertifikat' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);
    }

    private function authorizeManage(Request $request, RiwayatPendidikan $item): void
    {
        $user = $request->user();
        $personel = $user->personel;
        abort_unless($user->hasRole('admin') || ($personel && $item->personel_id === $personel->id), 403);
    }

    private function options(): array
    {
        return [
            'jenis' => ['AKADEMIK', 'DIKLAT', 'MILITER'],
            'jenjang' => ['SD', 'SMP', 'SMA', 'D3', 'D4', 'S1', 'S2', 'S3', 'PROFESI', 'LAIN'],
        ];
    }
}
