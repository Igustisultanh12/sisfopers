<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pju;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PjuManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Pju::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('jabatan_pju', 'like', "%{$search}%")
                  ->orWhere('satuan_wilayah', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role_pju', $request->role);
        }

        $pjuUsers = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/Pju/Index', [
            'pjuUsers'   => $pjuUsers,
            'filters'    => $request->only(['search', 'role']),
            'createdPju' => session('created_pju'),
        ]);
    }

    public function store(Request $request)
    {
        $pjuRoles = [
            'ka_bacadnas',
            'ses_bacadnas',
            'kapus_komcad',
            'pembina_matra',
            'pembina_kodam',
            'pembina_kodaeral',
            'pembina_kodau',
            'pembina_kodim',
            'pembina_lanal',
            'pembina_lanud',
        ];

        $validated = $request->validate([
            'full_name'      => 'required|string|max:150',
            'email'          => 'required|email|unique:pjus,email',
            'phone_number'   => 'required|string|max:20',
            'role'           => ['required', 'string', Rule::in($pjuRoles)],
            'jabatan_pju'    => 'required|string|max:150',
            'matra'          => 'nullable|in:AD,AL,AU',
            'satuan_wilayah' => 'nullable|string|max:150',
            'password'       => 'nullable|string|min:6',
        ]);

        try {
            $username = explode('@', $validated['email'])[0] . '_pju' . rand(100, 999);

            // Auto Generate Password jika tidak diisi manual
            $plainPassword = $validated['password'] ?? ('Pju@' . Str::random(6) . rand(10, 99));

            $pju = Pju::create([
                'uuid'           => Str::uuid(),
                'full_name'      => $validated['full_name'],
                'username'       => $username,
                'email'          => $validated['email'],
                'phone_number'   => $validated['phone_number'],
                'password'       => Hash::make($plainPassword),
                'role_pju'       => $validated['role'],
                'jabatan_pju'    => $validated['jabatan_pju'],
                'matra'          => $validated['matra'] ?? null,
                'satuan_wilayah' => $validated['satuan_wilayah'] ?? null,
                'is_active'      => true,
            ]);

            \App\Models\AuditLog::record('CREATE', 'Pju', $pju->id, null, [
                'full_name'   => $validated['full_name'],
                'email'       => $validated['email'],
                'role_pju'    => $validated['role'],
                'jabatan_pju' => $validated['jabatan_pju'],
            ]);

            return back()->with([
                'success'     => "Akun PJU {$validated['full_name']} ({$validated['jabatan_pju']}) berhasil dibuat secara otomatis.",
                'created_pju' => [
                    'id'          => $pju->id,
                    'full_name'   => $pju->full_name,
                    'jabatan_pju' => $pju->jabatan_pju,
                    'username'    => $username,
                    'email'       => $pju->email,
                    'password'    => $plainPassword,
                ]
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal membuat akun PJU: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, int $id)
    {
        $pju = Pju::findOrFail($id);

        $pjuRoles = [
            'ka_bacadnas',
            'ses_bacadnas',
            'kapus_komcad',
            'pembina_matra',
            'pembina_kodam',
            'pembina_kodaeral',
            'pembina_kodau',
            'pembina_kodim',
            'pembina_lanal',
            'pembina_lanud',
        ];

        $validated = $request->validate([
            'full_name'      => 'required|string|max:150',
            'email'          => 'required|email|unique:pjus,email,' . $pju->id,
            'phone_number'   => 'required|string|max:20',
            'role'           => ['required', 'string', Rule::in($pjuRoles)],
            'jabatan_pju'    => 'required|string|max:150',
            'matra'          => 'nullable|in:AD,AL,AU',
            'satuan_wilayah' => 'nullable|string|max:150',
            'password'       => 'nullable|string|min:6',
        ]);

        try {
            $updateData = [
                'full_name'      => $validated['full_name'],
                'email'          => $validated['email'],
                'phone_number'   => $validated['phone_number'],
                'role_pju'       => $validated['role'],
                'jabatan_pju'    => $validated['jabatan_pju'],
                'matra'          => $validated['matra'] ?? null,
                'satuan_wilayah' => $validated['satuan_wilayah'] ?? null,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $pju->update($updateData);

            \App\Models\AuditLog::record('UPDATE', 'Pju', $pju->id, null, [
                'full_name'   => $validated['full_name'],
                'role_pju'    => $validated['role'],
                'jabatan_pju' => $validated['jabatan_pju'],
            ]);

            return back()->with('success', "Akun PJU {$validated['full_name']} berhasil dimutakhirkan.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal memperbarui akun PJU: ' . $e->getMessage()]);
        }
    }

    public function destroy(int $id)
    {
        $pju = Pju::findOrFail($id);
        $name = $pju->full_name;

        try {
            $pju->delete();

            \App\Models\AuditLog::record('DELETE', 'Pju', $id, null, ['full_name' => $name]);

            return back()->with('success', "Akun PJU {$name} berhasil dihapus.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus akun PJU: ' . $e->getMessage()]);
        }
    }

    public function printAccountPdf(int $id)
    {
        $pju = Pju::findOrFail($id);
        $settings = \App\Models\Setting::pluck('value', 'key')->all();
        $plainPassword = request()->query('pass', '********');

        $html = view('pdf.pju_account', compact('pju', 'settings', 'plainPassword'))->render();

        return response($html)->header('Content-Type', 'text/html');
    }
}
