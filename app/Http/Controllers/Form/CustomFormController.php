<?php

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Models\CustomForm;
use App\Models\CustomFormResponse;
use App\Models\Personel;
use App\Models\MasterKepangkatan;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CustomFormController extends Controller
{
    /**
     * Mendapatkan user saat ini dari guard web atau pju
     */
    private function getCurrentUser()
    {
        if (Auth::guard('pju')->check()) {
            return [
                'user' => Auth::guard('pju')->user(),
                'guard' => 'pju',
                'role' => Auth::guard('pju')->user()->role_pju ?? 'pju',
                'name' => Auth::guard('pju')->user()->full_name,
            ];
        }

        $user = Auth::user();
        return [
            'user' => $user,
            'guard' => 'web',
            'role' => $user?->role?->name ?? 'admin',
            'name' => $user?->name ?? 'Administrator',
        ];
    }

    /**
     * Menampilkan daftar Formulir & Rekrutmen untuk Pengelola
     */
    public function index(Request $request)
    {
        $current = $this->getCurrentUser();

        $query = CustomForm::withCount('responses')
            ->latest();

        // Filter Pencarian
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter Status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter Kategori
        if ($request->filled('category') && $request->category !== 'ALL') {
            $query->where('category', $request->category);
        }

        // Jika koordinator matra, bisa difilter relevan dengan matranya
        if ($current['role'] === 'kordinator_matra' && isset($current['user']->matra) && $current['user']->matra) {
            $matra = strtoupper($current['user']->matra);
            $query->where(function ($q) use ($matra) {
                $q->where('target_matra', 'ALL')
                  ->orWhere('target_matra', $matra);
            });
        }

        $forms = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/Form/Index', [
            'forms' => $forms,
            'filters' => $request->only(['search', 'status', 'category']),
            'userRole' => $current['role'],
        ]);
    }

    /**
     * Menampilkan antarmuka pembuatan formulir baru
     */
    public function create()
    {
        $current = $this->getCurrentUser();

        // Pilihan Angkatan
        $angkatanList = Personel::whereNotNull('angkatan')
            ->distinct()
            ->orderBy('angkatan', 'desc')
            ->pluck('angkatan')
            ->filter()
            ->values();

        return Inertia::render('Admin/Form/Create', [
            'angkatanList' => $angkatanList,
            'userRole' => $current['role'],
        ]);
    }

    /**
     * Menyimpan formulir baru dan memicu notifikasi ke personel sasaran
     */
    public function store(Request $request)
    {
        $current = $this->getCurrentUser();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'target_rank_category' => 'required|string|in:ALL,PERWIRA,BINTARA,TAMTAMA',
            'target_matra' => 'required|string|in:ALL,AD,AL,AU',
            'target_angkatan' => 'required|string',
            'requirements' => 'nullable|array',
            'requirements.*.id' => 'required|string',
            'requirements.*.name' => 'required|string|max:255',
            'requirements.*.description' => 'nullable|string',
            'requirements.*.required' => 'boolean',
            'requirements.*.file_types' => 'nullable|array',
            'questions' => 'nullable|array',
            'questions.*.id' => 'required|string',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'required|string|in:multiple_choice,text,textarea',
            'questions.*.options' => 'nullable|array',
            'questions.*.required' => 'boolean',
        ]);

        $createdByUser = null;
        $createdByPju = null;

        if ($current['guard'] === 'pju') {
            $createdByPju = $current['user']->id;
        } else {
            $createdByUser = $current['user']?->id;
        }

        $form = CustomForm::create([
            'uuid' => (string) Str::uuid(),
            'title' => $validated['title'],
            'category' => strtoupper($validated['category']),
            'description' => $validated['description'] ?? '',
            'deadline' => $validated['deadline'] ?? null,
            'is_active' => true,
            'target_rank_category' => $validated['target_rank_category'],
            'target_matra' => $validated['target_matra'],
            'target_angkatan' => $validated['target_angkatan'],
            'requirements' => $validated['requirements'] ?? [],
            'questions' => $validated['questions'] ?? [],
            'created_by_user_id' => $createdByUser,
            'created_by_pju_id' => $createdByPju,
            'creator_role' => $current['role'],
        ]);

        // Pemicu Notifikasi ke Personel Sasaran yang Memenuhi Syarat
        $this->notifyTargetedPersonels($form);

        // Menentukan rute redirect berdasarkan role
        $redirectRoute = match ($current['role']) {
            'ka_bacadnas', 'ses_bacadnas', 'kapus_komcad', 'pembina_matra',
            'pembina_kodam', 'pembina_kodaeral', 'pembina_kodau', 'pembina_kodim',
            'pembina_lanal', 'pembina_lanud' => 'pju.form.index',
            'kordinator_matra', 'kordinator_angkatan' => 'kordinator.form.index',
            default => 'admin.form.index',
        };

        return redirect()->route($redirectRoute)->with('success', 'Formulir berhasil diterbitkan dan notifikasi telah dikirimkan ke personel sasaran.');
    }

    /**
     * Mengirimkan notifikasi lonceng & WA ke personel yang sesuai kriteria target
     */
    private function notifyTargetedPersonels(CustomForm $form)
    {
        try {
            $personelQuery = Personel::with('user')->whereHas('user', function ($q) {
                $q->where('is_active', true);
            });

            // Filter Matra
            if ($form->target_matra !== 'ALL' && !empty($form->target_matra)) {
                $personelQuery->where('matra', $form->target_matra);
            }

            // Filter Angkatan
            if ($form->target_angkatan !== 'ALL' && !empty($form->target_angkatan)) {
                $personelQuery->where('angkatan', $form->target_angkatan);
            }

            // Filter Kelompok Kepangkatan
            if ($form->target_rank_category !== 'ALL' && !empty($form->target_rank_category)) {
                $expectedKelompok = match (strtoupper($form->target_rank_category)) {
                    'PERWIRA' => '1',
                    'BINTARA' => '2',
                    'TAMTAMA' => '3',
                    default => null,
                };

                if ($expectedKelompok) {
                    $matchingRanks = MasterKepangkatan::where('kelompok_nikc', $expectedKelompok)
                        ->where('is_active', true)
                        ->pluck('nama')
                        ->toArray();

                    // Tangani kemungkinan format pangkat tanpa suffix ' KC'
                    $cleanRanks = array_map(function ($r) {
                        return str_replace(' KC', '', $r);
                    }, $matchingRanks);
                    $allMatchingRanks = array_unique(array_merge($matchingRanks, $cleanRanks));

                    $personelQuery->where(function ($q) use ($allMatchingRanks) {
                        foreach ($allMatchingRanks as $rk) {
                            $q->orWhere('pangkat', 'like', "%{$rk}%");
                        }
                    });
                }
            }

            $personels = $personelQuery->get();

            $deadlineText = $form->deadline ? $form->deadline->translatedFormat('d F Y H:i') . ' WIB' : 'Tidak ditentukan';
            $notifTitle = "Formulir Baru: " . $form->title;
            $notifMessage = "Terdapat pengumuman/formulir baru kategori [{$form->category}] yang ditujukan kepada Anda. Batas waktu pengisian: {$deadlineText}. Silakan buka menu Formulir & Rekrutmen untuk melengkapi persyaratan.";
            $notifUrl = route('personel.form.show', $form->uuid);

            foreach ($personels as $pers) {
                if ($pers->user) {
                    $pers->user->notify(new \App\Notifications\CustomFormAppAndEmailNotification($notifTitle, $notifMessage, $notifUrl, 'form'));
                }
            }
        } catch (\Exception $e) {
            Log::error("Gagal mengirim notifikasi formulir sasaran: " . $e->getMessage());
        }
    }

    /**
     * Form edit
     */
    public function edit($uuid)
    {
        $current = $this->getCurrentUser();
        $form = CustomForm::where('uuid', $uuid)->firstOrFail();

        $angkatanList = Personel::whereNotNull('angkatan')
            ->distinct()
            ->orderBy('angkatan', 'desc')
            ->pluck('angkatan')
            ->filter()
            ->values();

        return Inertia::render('Admin/Form/Edit', [
            'form' => $form,
            'angkatanList' => $angkatanList,
            'userRole' => $current['role'],
        ]);
    }

    /**
     * Memperbarui formulir
     */
    public function update(Request $request, $uuid)
    {
        $current = $this->getCurrentUser();
        $form = CustomForm::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'target_rank_category' => 'required|string|in:ALL,PERWIRA,BINTARA,TAMTAMA',
            'target_matra' => 'required|string|in:ALL,AD,AL,AU',
            'target_angkatan' => 'required|string',
            'requirements' => 'nullable|array',
            'requirements.*.id' => 'required|string',
            'requirements.*.name' => 'required|string|max:255',
            'requirements.*.description' => 'nullable|string',
            'requirements.*.required' => 'boolean',
            'requirements.*.file_types' => 'nullable|array',
            'questions' => 'nullable|array',
            'questions.*.id' => 'required|string',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'required|string|in:multiple_choice,text,textarea',
            'questions.*.options' => 'nullable|array',
            'questions.*.required' => 'boolean',
        ]);

        $form->update([
            'title' => $validated['title'],
            'category' => strtoupper($validated['category']),
            'description' => $validated['description'] ?? '',
            'deadline' => $validated['deadline'] ?? null,
            'target_rank_category' => $validated['target_rank_category'],
            'target_matra' => $validated['target_matra'],
            'target_angkatan' => $validated['target_angkatan'],
            'requirements' => $validated['requirements'] ?? [],
            'questions' => $validated['questions'] ?? [],
        ]);

        $redirectRoute = match ($current['role']) {
            'ka_bacadnas', 'ses_bacadnas', 'kapus_komcad', 'pembina_matra',
            'pembina_kodam', 'pembina_kodaeral', 'pembina_kodau', 'pembina_kodim',
            'pembina_lanal', 'pembina_lanud' => 'pju.form.index',
            'kordinator_matra', 'kordinator_angkatan' => 'kordinator.form.index',
            default => 'admin.form.index',
        };

        return redirect()->route($redirectRoute)->with('success', 'Formulir berhasil diperbarui.');
    }

    /**
     * Buka / Tutup Formulir
     */
    public function toggleStatus($uuid)
    {
        $form = CustomForm::where('uuid', $uuid)->firstOrFail();
        $form->is_active = !$form->is_active;
        $form->save();

        $statusText = $form->is_active ? 'diaktifkan kembali' : 'ditutup';
        return back()->with('success', "Formulir berhasil {$statusText}.");
    }

    /**
     * Hapus formulir
     */
    public function destroy($uuid)
    {
        $form = CustomForm::where('uuid', $uuid)->firstOrFail();
        $form->delete();

        return back()->with('success', 'Formulir berhasil dihapus.');
    }

    /**
     * Menampilkan daftar respon personel pengisi formulir
     */
    public function responses(Request $request, $uuid)
    {
        $current = $this->getCurrentUser();
        $form = CustomForm::where('uuid', $uuid)->firstOrFail();

        $query = CustomFormResponse::with(['personel.user', 'personel.sinyalmen', 'personel.jobHistories'])
            ->where('form_id', $form->id)
            ->latest('submitted_at');

        // Filter status seleksi
        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        }

        // Filter pencarian nama / NIKC
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('personel', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nikc', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $responses = $query->paginate(15)->withQueryString();

        // Statistik
        $stats = [
            'total' => CustomFormResponse::where('form_id', $form->id)->count(),
            'submitted' => CustomFormResponse::where('form_id', $form->id)->where('status', 'SUBMITTED')->count(),
            'verified' => CustomFormResponse::where('form_id', $form->id)->where('status', 'VERIFIED')->count(),
            'accepted' => CustomFormResponse::where('form_id', $form->id)->where('status', 'ACCEPTED')->count(),
            'rejected' => CustomFormResponse::where('form_id', $form->id)->where('status', 'REJECTED')->count(),
        ];

        return Inertia::render('Admin/Form/Responses', [
            'form' => $form,
            'responses' => $responses,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status']),
            'userRole' => $current['role'],
        ]);
    }

    /**
     * Mengambil detail satu respon personel (Data diri lengkap, riwayat pendidikan, berkas & jawaban)
     */
    public function responseDetail($uuid, $responseUuid)
    {
        $form = CustomForm::where('uuid', $uuid)->firstOrFail();
        $response = CustomFormResponse::with([
            'personel.user',
            'personel.sinyalmen',
            'personel.jobHistories',
            'personel.riwayatPendidikan'
        ])
        ->where('form_id', $form->id)
        ->where('uuid', $responseUuid)
        ->firstOrFail();

        return response()->json([
            'success' => true,
            'form' => $form,
            'response' => $response,
        ]);
    }

    /**
     * Memperbarui status verifikasi/seleksi respon personel
     */
    public function updateResponseStatus(Request $request, $uuid, $responseUuid)
    {
        $current = $this->getCurrentUser();
        $form = CustomForm::where('uuid', $uuid)->firstOrFail();
        $response = CustomFormResponse::with('personel.user')
            ->where('form_id', $form->id)
            ->where('uuid', $responseUuid)
            ->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|string|in:SUBMITTED,VERIFIED,ACCEPTED,REJECTED',
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        $response->update([
            'status' => $validated['status'],
            'verification_notes' => $validated['verification_notes'] ?? null,
            'verified_by' => $current['name'] . " ({$current['role']})",
            'verified_at' => now(),
        ]);

        // Kirim notifikasi status pembaruan ke personel di aplikasi dan email (tanpa WhatsApp)
        if ($response->personel?->user) {
            $statusLabel = match ($validated['status']) {
                'VERIFIED' => 'Telah Diverifikasi',
                'ACCEPTED' => 'Diterima / Lolos Seleksi',
                'REJECTED' => 'Tidak Lolos / Berkas Ditolak',
                default => 'Sedang Ditinjau',
            };

            $notifTitle = "Pembaruan Status Formulir: " . $form->title;
            $notifMessage = ($validated['status'] === 'ACCEPTED')
                ? "Selamat! Anda dinyatakan LOLOS SELEKSI / DITERIMA pada '{$form->title}'. Harap memantau instruksi dan arahan dinas selanjutnya melalui portal SISFOPERSKC." . ($validated['verification_notes'] ? " Catatan Petugas: {$validated['verification_notes']}" : "")
                : "Status pengajuan Anda pada formulir [{$form->title}] telah diperbarui menjadi: {$statusLabel}." . ($validated['verification_notes'] ? " Catatan: {$validated['verification_notes']}" : "");

            $response->personel->user->notify(new \App\Notifications\CustomFormAppAndEmailNotification(
                $notifTitle,
                $notifMessage,
                route('personel.form.show', $form->uuid),
                'check-circle'
            ));
        }

        return back()->with('success', 'Status respon personel berhasil diperbarui.');
    }
}
