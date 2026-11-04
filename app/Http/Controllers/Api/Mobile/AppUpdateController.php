<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Jobs\SendMobilePush;
use App\Models\MobileDevice;
use App\Services\FcmService;
use App\Support\AppRelease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Pembaruan aplikasi Android: Admin mengunggah APK di Pengaturan Sistem,
 * aplikasi di HP memeriksa & mengunduhnya dari server ini.
 */
class AppUpdateController extends Controller
{
    protected function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->role?->name === 'admin', 403, 'Hanya Administrator yang dapat mengelola pembaruan aplikasi.');
    }

    /** Admin: unggah APK versi baru. */
    public function store(Request $request)
    {
        $this->ensureAdmin($request);
        if (!$request->hasFile('apk')) {
            $limit = ini_get('upload_max_filesize') ?: '?';
            $post = ini_get('post_max_size') ?: '?';
            $f = $request->files->get('apk');
            $tooBig = $f instanceof \Symfony\Component\HttpFoundation\File\UploadedFile
                && in_array($f->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);
            return response()->json([
                'success' => false,
                'message' => $tooBig
                    ? "APK gagal diterima server: ukuran melebihi batas unggah PHP (upload_max_filesize {$limit}, post_max_size {$post}). Naikkan keduanya (mis. 100M) serta client_max_body_size Nginx."
                    : 'Pilih berkas APK yang akan diunggah.',
            ], 422);
        }

        $request->validate([
            'apk' => 'required|file|max:204800',
            'notes' => 'nullable|string|max:2000',
            'version' => 'nullable|string|max:20',
            'notify' => 'nullable',
        ], [
            'apk.uploaded' => 'APK gagal diunggah (melebihi batas upload_max_filesize server).',
            'apk.max' => 'Ukuran APK maksimal 200 MB.',
        ]);

        $file = $request->file('apk');
        if (strtolower($file->getClientOriginalExtension()) !== 'apk') {
            return response()->json(['success' => false, 'message' => 'Berkas harus berekstensi .apk.'], 422);
        }

        $actor = $request->user() ?: auth('pju')->user();
        $uploader = $actor?->personel?->full_name ?? $actor?->full_name ?? $actor?->username ?? 'Admin';
        $result = AppRelease::store($file, (string) $request->input('notes', ''), (string) $uploader, $request->input('version'));
        if (is_string($result)) {
            return response()->json(['success' => false, 'message' => $result], 422);
        }

        $notified = 0;
        $notify = filter_var($request->input('notify', true), FILTER_VALIDATE_BOOLEAN);
        if ($notify && FcmService::enabled()) {
            $recipients = MobileDevice::query()
                ->select('tokenable_type', 'tokenable_id')
                ->distinct()
                ->get()
                ->map(fn ($d) => [$d->tokenable_type, (int) $d->tokenable_id])
                ->all();
            $notified = count($recipients);
            if ($notified > 0) {
                SendMobilePush::dispatchAfterResponse(
                    $recipients,
                    "Pembaruan SISFOPERS KC {$result['version']} tersedia",
                    mb_substr(trim($result['notes']) !== '' ? trim($result['notes']) : 'Ketuk untuk mengunduh dan memasang versi terbaru.', 0, 240),
                    ['version' => $result['version'], 'url' => '/account/settings'],
                    'app_update'
                );
            }
            AppRelease::markNotified($notified);
        }

        try {
            \App\Models\AuditLog::create([
                'user_id' => $request->user()?->id,
                'action' => 'UPLOAD_APP_UPDATE',
                'model_type' => 'AndroidApp',
                'model_id' => 0,
                'new_values' => ['version' => $result['version'], 'size' => $result['size']],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // audit opsional
        }

        $msg = "APK SISFOPERS KC versi {$result['version']} berhasil diunggah.";
        $msg .= $notified > 0
            ? " Pemberitahuan pembaruan dikirim ke {$notified} pengguna aplikasi."
            : ($notify ? ' Notifikasi push belum aktif; aplikasi akan menemukan pembaruan saat dibuka.' : '');

        return response()->json(['success' => true, 'message' => $msg, 'app_update' => AppRelease::adminSummary()]);
    }

    /** Admin: hapus APK yang diunggah. */
    public function destroy(Request $request)
    {
        $this->ensureAdmin($request);
        AppRelease::clear();
        return response()->json(['success' => true, 'message' => 'APK pembaruan dihapus dari server.', 'app_update' => null]);
    }

    /** Admin: ringkasan versi aktif. */
    public function status(Request $request)
    {
        $this->ensureAdmin($request);
        return response()->json([
            'success' => true,
            'app_update' => AppRelease::adminSummary(),
            'upload_limit' => AppRelease::uploadLimitBytes(),
        ]);
    }

    /** Aplikasi: informasi versi terbaru (publik). */
    public function latest()
    {
        return response()->json(AppRelease::publicPayload())->header('Cache-Control', 'no-store');
    }

    /** Aplikasi: unduh APK terbaru (publik). */
    public function download()
    {
        $m = AppRelease::current();
        if (!$m) {
            return response()->json(['success' => false, 'message' => 'Belum ada pembaruan aplikasi di server.'], 404);
        }
        $path = Storage::disk(AppRelease::DISK)->path($m['path']);
        return response()->download($path, 'SISFOPERSKC-' . $m['version'] . '.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Cache-Control' => 'no-store',
            'X-Content-SHA256' => (string) $m['sha256'],
        ]);
    }
}
