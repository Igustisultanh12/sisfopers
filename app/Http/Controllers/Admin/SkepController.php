<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterKepangkatan;
use App\Models\SkepData;
use App\Models\SkepRequest;
use App\Rules\NikcFormatRule;
use App\Jobs\SendWhatsappNotificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Inertia\Inertia;

class SkepController extends Controller
{
    /**
     * Menampilkan Dasbor Manajemen SKEP Admin
     */
    public function index(Request $request)
    {
        $dataQuery = SkepData::query();
        
        if ($request->filled('search')) {
            $search = $request->search;
            $dataQuery->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%')
                  ->orWhere('nikc', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('matra')) {
            $dataQuery->where('matra', $request->matra);
        }

        $skepData = $dataQuery->latest()->paginate(10, ['*'], 'skep_page')->withQueryString();

        $requestQuery = SkepRequest::with('verifier');
        
        if ($request->filled('search_request')) {
            $searchReq = $request->search_request;
            $requestQuery->where(function ($q) use ($searchReq) {
                $q->where('nama_lengkap', 'like', '%' . $searchReq . '%')
                  ->orWhere('nikc', 'like', '%' . $searchReq . '%')
                  ->orWhere('nik', 'like', '%' . $searchReq . '%');
            });
        }

        $skepRequests = $requestQuery->latest()->paginate(10, ['*'], 'request_page')->withQueryString();

        return Inertia::render('Admin/Skep/Index', [
            'skepData' => $skepData,
            'skepRequests' => $skepRequests,
            'filters' => $request->only(['search', 'matra', 'search_request'])
        ]);
    }

    /**
     * Mengimpor data SKEP dari file Excel (.xls, .xlsx)
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx|max:5120',
        ]);

        $file = $request->file('file');
        
        DB::beginTransaction();
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Ambil header dan buang
            array_shift($rows);

            foreach ($rows as $index => $row) {
                // Lewati baris jika kolom penting kosong
                if (empty($row[0]) || empty($row[1])) {
                    continue;
                }

                $nikc = SkepData::reconstructNikc(trim((string) $row[1]));
                $dob = $this->normalizeExcelDate($row[5] ?? null);
                if (!$dob) {
                    throw new \InvalidArgumentException('Tanggal lahir pada baris ' . ($index + 2) . ' file SKEP wajib diisi dengan format tanggal yang valid.');
                }

                $pangkat = MasterKepangkatan::canonicalName(trim((string) ($row[2] ?? 'Prada KC')));

                $nikcValidator = Validator::make(
                    ['nikc' => $nikc, 'pangkat' => $pangkat],
                    [
                        'nikc' => ['required', 'min:15', 'max:18', 'string', new NikcFormatRule(null, $dob, $pangkat)],
                        'pangkat' => ['required', \Illuminate\Validation\Rule::exists('master_kepangkatan', 'nama')->where('is_active', true)],
                    ]
                );

                if ($nikcValidator->fails()) {
                    throw new \InvalidArgumentException('Data pada baris ' . ($index + 2) . ' file SKEP tidak valid: ' . $nikcValidator->errors()->first());
                }

                // Hapus data lama yang memiliki NIKC sama terlebih dahulu untuk menimpa bersih
                SkepData::where('nikc', $nikc)->delete();

                // Kolom: Nama Lengkap (0), NIKC (1), Pangkat (2), Angkatan (3), Matra (4), Tanggal Lahir (5)
                SkepData::create([
                    'nikc' => $nikc,
                    'nama_lengkap' => trim($row[0]),
                    'dob' => $dob,
                    'pangkat' => $pangkat,
                    'angkatan' => trim($row[3] ?? date('Y')),
                    'matra' => strtoupper(trim($row[4] ?? 'AD')),
                ]);
            }

            DB::commit();
            return redirect()->route('admin.skep.index')->with('success', 'Data SKEP berhasil diimpor dari Excel.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal mengimpor file: ' . $e->getMessage()]);
        }
    }

    /**
     * Menghapus baris data SKEP tertentu
     */
    public function destroy($id)
    {
        try {
            $skep = SkepData::findOrFail($id);
            $skep->delete();
            return redirect()->route('admin.skep.index')->with('success', 'Data SKEP berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus data SKEP.']);
        }
    }

    /**
     * Melakukan verifikasi (Persetujuan / Penolakan) berkas SKEP PDF pendaftar
     */
    public function verify(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:APPROVED,REJECTED',
            'admin_notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $skepRequest = SkepRequest::findOrFail($id);
            
            $skepRequest->update([
                'status' => $request->status,
                'admin_notes' => $request->admin_notes,
                'verified_by' => Auth::id(),
                'verified_at' => now()
            ]);

            if ($request->status === 'APPROVED') {
                // Masukkan data pengaju secara otomatis ke dalam database SKEP yang valid
                SkepData::updateOrCreate(
                    ['nikc' => $skepRequest->nikc],
                    [
                        'nama_lengkap' => $skepRequest->nama_lengkap,
                        'dob' => $skepRequest->dob,
                        'pangkat' => $skepRequest->pangkat,
                        'angkatan' => $skepRequest->angkatan,
                        'matra' => $skepRequest->matra,
                    ]
                );

                // Kirim notifikasi WhatsApp & Email sukses
                $msg = "Halo *{$skepRequest->nama_lengkap}*,\n\nPengajuan verifikasi berkas SKEP Anda dengan NIKC *{$skepRequest->nikc}* telah *DISETUJUI* oleh Admin. Anda sekarang dapat melanjutkan proses pendaftaran di web Sisfoperskc.\n\nSilakan daftar di link berikut:\n" . route('register');
                \App\Services\WhatsappService::sendMessage($skepRequest->phone_number, $msg);

                if ($skepRequest->email) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($skepRequest->email)->send(
                            new \App\Mail\OtpNotificationMail(
                                $skepRequest->nama_lengkap,
                                $skepRequest->nikc,
                                'SKEP-OK',
                                'Verifikasi Berkas SKEP Disetujui',
                                'Silakan Mendaftar'
                            )
                        );
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Gagal kirim email skep approval: " . $e->getMessage());
                    }
                }
            } else {
                // Kirim notifikasi WhatsApp & Email penolakan
                $catatan = $request->admin_notes ?? 'Berkas tidak sesuai atau kurang jelas.';
                $msg = "Halo *{$skepRequest->nama_lengkap}*,\n\nPengajuan verifikasi berkas SKEP Anda dengan NIKC *{$skepRequest->nikc}* telah *DITOLAK* oleh Admin dengan catatan:\n_\"{$catatan}\"_\n\nSilakan unggah kembali berkas SKEP yang valid di halaman pendaftaran.";
                \App\Services\WhatsappService::sendMessage($skepRequest->phone_number, $msg);

                if ($skepRequest->email) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($skepRequest->email)->send(
                            new \App\Mail\OtpNotificationMail(
                                $skepRequest->nama_lengkap,
                                $skepRequest->nikc,
                                'SKEP-REJECT',
                                'Verifikasi Berkas SKEP Ditolak (' . $catatan . ')',
                                'Silakan Ajukan Ulang'
                            )
                        );
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Gagal kirim email skep rejection: " . $e->getMessage());
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.skep.index')->with('success', 'Verifikasi berkas SKEP berhasil diproses.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memproses verifikasi berkas: ' . $e->getMessage()]);
        }
    }

    private function normalizeExcelDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            }

            return Carbon::parse($value)->toDateString();
        } catch (\Exception) {
            return null;
        }
    }
}
