<?php

namespace App\Http\Controllers\Personel;

use App\Http\Controllers\Controller;
use App\Models\CustomForm;
use App\Models\CustomFormResponse;
use App\Models\Personel;
use App\Models\AuditLog;
use App\Mail\SystemNotificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PersonelFormController extends Controller
{
    /**
     * Mengambil profil Personel dari user yang sedang login
     */
    private function getPersonel(): Personel
    {
        $user = Auth::user();
        return Personel::with(['user', 'sinyalmen', 'riwayatPendidikan'])
            ->where('user_id', $user->id)
            ->firstOrFail();
    }

    /**
     * Menampilkan daftar formulir dan rekrutmen yang tersedia untuk personel bersangkutan
     */
    public function index()
    {
        $personel = $this->getPersonel();

        // Ambil semua formulir yang aktif atau pernah diisi oleh personel ini
        $allForms = CustomForm::with(['responses' => function ($q) use ($personel) {
            $q->where('personel_id', $personel->id);
        }])
        ->latest()
        ->get();

        // Filter formulir yang relevan dan eligible untuk personel ini
        $eligibleForms = $allForms->filter(function ($form) use ($personel) {
            return $form->isPersonelEligible($personel) || $form->responses->isNotEmpty();
        })->values();

        // Format data untuk antarmuka
        $formsData = $eligibleForms->map(function ($form) use ($personel) {
            $userResponse = $form->responses->first();
            $isSubmitted = !is_null($userResponse);
            $isDeadlinePassed = $form->deadline && now()->greaterThan($form->deadline);

            return [
                'id' => $form->id,
                'uuid' => $form->uuid,
                'title' => $form->title,
                'category' => $form->category,
                'description' => $form->description,
                'deadline' => $form->deadline ? $form->deadline->translatedFormat('d F Y H:i') . ' WIB' : null,
                'is_active' => $form->is_active,
                'is_submitted' => $isSubmitted,
                'is_deadline_passed' => $isDeadlinePassed,
                'submission_status' => $userResponse?->status,
                'submission_date' => $userResponse?->submitted_at?->translatedFormat('d F Y H:i') . ' WIB',
                'target_rank_category' => $form->target_rank_category,
                'target_matra' => $form->target_matra,
            ];
        });

        return Inertia::render('Personel/Form/Index', [
            'forms' => $formsData,
            'personel' => $personel,
            'hasCompletedEducation' => $this->checkEducationCompleteness($personel),
            'ineligibleError' => session('ineligible_error'),
        ]);
    }

    /**
     * Menampilkan detail formulir dengan data diri terisi otomatis (Auto-Fill)
     */
    public function show($uuid)
    {
        $personel = $this->getPersonel();
        $form = CustomForm::where('uuid', $uuid)->firstOrFail();

        // Validasi Kelayakan Sasaran Form (Kepangkatan & Matra)
        if (!$form->isPersonelEligible($personel) && !$form->hasPersonelSubmitted($personel)) {
            $targetRank = match ($form->target_rank_category) {
                'PERWIRA' => 'jenjang Perwira',
                'BINTARA' => 'jenjang Bintara',
                'TAMTAMA' => 'jenjang Tamtama',
                default => 'kriteria tertentu',
            };

            $msg = "Mohon Maaf form ini hanya ditujukan kepada {$targetRank}";
            if ($form->target_matra && $form->target_matra !== 'ALL') {
                $msg .= " Matra {$form->target_matra}";
            }

            return redirect()->route('personel.form.index')->with('ineligible_error', $msg);
        }

        // Cek apakah personel sudah pernah mengirim respon
        $existingResponse = CustomFormResponse::where('form_id', $form->id)
            ->where('personel_id', $personel->id)
            ->first();

        $isDeadlinePassed = $form->deadline && now()->greaterThan($form->deadline);
        $isClosed = !$form->is_active || ($isDeadlinePassed && !$existingResponse);

        return Inertia::render('Personel/Form/Show', [
            'form' => $form,
            'personel' => $personel,
            'existingResponse' => $existingResponse,
            'isClosed' => $isClosed,
            'isDeadlinePassed' => $isDeadlinePassed,
            'hasCompletedEducation' => $this->checkEducationCompleteness($personel),
        ]);
    }

    /**
     * Pengecekan apakah personel telah melengkapi riwayat pendidikan
     */
    private function checkEducationCompleteness(Personel $personel): bool
    {
        return $personel->riwayatPendidikan()
            ->where(function ($query) {
                $query->whereIn('jenis', ['AKADEMIK', 'UMUM'])
                      ->orWhere('jenis', '!=', 'MILITER');
            })
            ->exists();
    }

    /**
     * Memproses penyimpanan respon personel, unggah berkas privat & enkripsi kuesioner
     */
    public function submit(Request $request, $uuid)
    {
        $personel = $this->getPersonel();
        $form = CustomForm::where('uuid', $uuid)->firstOrFail();

        // 1. Verifikasi Kelayakan Sasaran Form
        if (!$form->isPersonelEligible($personel)) {
            abort(403, 'Akses ditolak: Anda tidak termasuk dalam target sasaran kepangkatan atau matra formulir ini.');
        }

        // 2. Verifikasi Status Aktif & Tenggat Waktu
        if (!$form->is_active) {
            return back()->withErrors(['error' => 'Formulir ini telah dinonaktifkan oleh administrator.']);
        }
        if ($form->deadline && now()->greaterThan($form->deadline)) {
            return back()->withErrors(['error' => 'Batas waktu pengisian formulir ini telah berakhir.']);
        }

        // 3. Pencegahan Pengisian Ganda
        if ($form->hasPersonelSubmitted($personel)) {
            return back()->withErrors(['error' => 'Anda telah mengirimkan respon pada formulir ini sebelumnya.']);
        }

        // 4. Validasi & Pengunggahan Berkas Persyaratan ke Storage Privat
        $requirements = $form->requirements ?? [];
        $uploadedFiles = [];

        foreach ($requirements as $req) {
            $reqId = $req['id'];
            $fieldName = "req_{$reqId}";
            $isRequired = !empty($req['required']);

            if ($isRequired && !$request->hasFile($fieldName)) {
                return back()->withErrors([$fieldName => "Berkas persyaratan [{$req['name']}] wajib diunggah."]);
            }

            if ($request->hasFile($fieldName)) {
                $file = $request->file($fieldName);

                // Validasi ekstensi yang diizinkan (PDF, JPG, JPEG, PNG)
                $allowedExtensions = $req['file_types'] ?? ['pdf', 'jpg', 'jpeg', 'png'];
                $ext = strtolower($file->getClientOriginalExtension());
                if (!in_array($ext, array_map('strtolower', $allowedExtensions))) {
                    return back()->withErrors([$fieldName => "Format berkas [{$req['name']}] harus berupa: " . implode(', ', $allowedExtensions)]);
                }

                // Validasi ukuran maksimal (Default 5MB)
                $maxSize = ($req['max_size_kb'] ?? 5120) * 1024;
                if ($file->getSize() > $maxSize) {
                    return back()->withErrors([$fieldName => "Ukuran berkas [{$req['name']}] melebihi batas maksimal " . ($req['max_size_kb'] ?? 5120) . " KB."]);
                }

                // Simpan ke storage privat terenkripsi/terisolasi
                $storedPath = $file->store("personel/form_requirements/{$form->uuid}/{$personel->id}", 'private');

                $uploadedFiles[$reqId] = [
                    'req_id' => $reqId,
                    'name' => $req['name'],
                    'file_path' => $storedPath,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        }

        // 5. Validasi & Penyusunan Jawaban Kuesioner Pertanyaan
        $questions = $form->questions ?? [];
        $answers = [];

        foreach ($questions as $q) {
            $qId = $q['id'];
            $fieldName = "q_{$qId}";
            $isRequired = !empty($q['required']);
            $val = $request->input($fieldName);

            if ($isRequired && (is_null($val) || trim((string)$val) === '')) {
                return back()->withErrors([$fieldName => "Pertanyaan nomor ini wajib dijawab."]);
            }

            $answers[$qId] = [
                'question_id' => $qId,
                'question' => $q['question'],
                'type' => $q['type'],
                'answer' => $val,
            ];
        }

        // 6. Simpan Respon (Jawaban Terenkripsi Otomatis AES-256-CBC)
        $response = CustomFormResponse::create([
            'uuid' => (string) Str::uuid(),
            'form_id' => $form->id,
            'personel_id' => $personel->id,
            'submitted_at' => now(),
            'status' => 'SUBMITTED',
            'uploaded_files' => $uploadedFiles,
            'answers' => $answers, // Terenkripsi otomatis melalui cast 'encrypted:array'
        ]);

        // 7. Audit Log Aktivitas
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'SUBMIT_CUSTOM_FORM',
            'model_type' => CustomForm::class,
            'model_id' => $form->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // 8. Pengiriman Notifikasi Konfirmasi Penerimaan Berkas & Jawaban (Email & In-App)
        try {
            $notifTitle = 'Pengiriman Formulir Berhasil: ' . $form->title;
            $notifMessage = "Terima kasih. Jawaban kuesioner dan berkas persyaratan Anda untuk '{$form->title}' telah berhasil disimpan di pangkalan data SISFOPERSKC dan saat ini sedang menunggu proses verifikasi oleh tim pembina / panitia.";
            $notifUrl = route('personel.form.show', $form->uuid);

            if ($personel->user) {
                $personel->user->notify(new \App\Notifications\CustomFormAppAndEmailNotification(
                    $notifTitle,
                    $notifMessage,
                    $notifUrl,
                    'form'
                ));
            }
        } catch (\Throwable $e) {
            Log::error("Gagal mengirimkan notifikasi konfirmasi formulir: " . $e->getMessage());
        }

        return redirect()->route('personel.form.show', $form->uuid)->with('success', 'Jawaban dan berkas persyaratan Anda telah berhasil disimpan dan saat ini sedang menunggu proses verifikasi.');
    }
}
