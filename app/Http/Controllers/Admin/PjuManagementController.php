<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Personel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PjuManagementController extends Controller
{
    public function index(Request $request)
    {
        $pjuRoleNames = [
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

        $query = User::whereHas('role', function ($q) use ($pjuRoleNames) {
            $q->whereIn('name', $pjuRoleNames);
        })->with(['role', 'personel']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('jabatan_pju', 'like', "%{$search}%")
                  ->orWhere('satuan_wilayah', 'like', "%{$search}%")
                  ->orWhereHas('personel', function ($p) use ($search) {
                      $p->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('role', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $pjuUsers = $query->latest()->paginate(15)->withQueryString();

        $roles = Role::whereIn('name', $pjuRoleNames)->get(['id', 'name']);

        return Inertia::render('Admin/Pju/Index', [
            'pjuUsers' => $pjuUsers,
            'roles'    => $roles,
            'filters'  => $request->only(['search', 'role']),
        ]);
    }

    public function store(Request $request)
    {
        $pjuRoleNames = [
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
            'email'          => 'required|email|unique:users,email',
            'phone_number'   => 'required|string|max:20',
            'role'           => ['required', 'string', Rule::in($pjuRoleNames)],
            'jabatan_pju'    => 'required|string|max:150',
            'matra'          => 'nullable|in:AD,AL,AU',
            'satuan_wilayah' => 'nullable|string|max:150',
            'password'       => 'required|string|min:8',
        ]);

        DB::beginTransaction();
        try {
            $role = Role::where('name', $validated['role'])->firstOrFail();

            $user = User::create([
                'uuid'           => Str::uuid(),
                'role_id'        => $role->id,
                'username'       => explode('@', $validated['email'])[0] . '_' . rand(100, 999),
                'email'          => $validated['email'],
                'password'       => Hash::make($validated['password']),
                'matra'          => $validated['matra'] ?? null,
                'jabatan_pju'    => $validated['jabatan_pju'],
                'satuan_wilayah' => $validated['satuan_wilayah'] ?? null,
                'is_active'      => true,
            ]);

            // Buat entitas Personel pendamping agar data profil PJU terkelola dengan baik
            $personel = Personel::create([
                'uuid'         => Str::uuid(),
                'user_id'      => $user->id,
                'full_name'    => $validated['full_name'],
                'nik'          => 'PJU' . rand(100000000000, 999999999999),
                'email'        => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'matra'        => $validated['matra'] ?? 'AD',
                'angkatan'     => date('Y'),
                'pangkat'      => 'Pejabat Utama',
                'dob'          => '1980-01-01',
                'province'     => 'DKI Jakarta',
                'status_profile' => 'LENGKAP',
                'face_verified'  => true,
                'pob' => '-', 'address' => '-', 'city' => '-', 'district' => '-', 'village' => '-', 'postal_code' => '-'
            ]);

            DB::commit();

            \App\Models\AuditLog::record('CREATE', 'User', $user->id, null, [
                'full_name'   => $validated['full_name'],
                'email'       => $validated['email'],
                'role'        => $validated['role'],
                'jabatan_pju' => $validated['jabatan_pju'],
            ]);

            return back()->with('success', "Akun PJU {$validated['full_name']} ({$validated['jabatan_pju']}) berhasil dibuat.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat akun PJU: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, int $id)
    {
        $user = User::with('personel')->findOrFail($id);

        $pjuRoleNames = [
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
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'phone_number'   => 'required|string|max:20',
            'role'           => ['required', 'string', Rule::in($pjuRoleNames)],
            'jabatan_pju'    => 'required|string|max:150',
            'matra'          => 'nullable|in:AD,AL,AU',
            'satuan_wilayah' => 'nullable|string|max:150',
            'password'       => 'nullable|string|min:8',
        ]);

        DB::beginTransaction();
        try {
            $role = Role::where('name', $validated['role'])->firstOrFail();

            $updateData = [
                'role_id'        => $role->id,
                'email'          => $validated['email'],
                'matra'          => $validated['matra'] ?? null,
                'jabatan_pju'    => $validated['jabatan_pju'],
                'satuan_wilayah' => $validated['satuan_wilayah'] ?? null,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            if ($user->personel) {
                $user->personel->update([
                    'full_name'    => $validated['full_name'],
                    'email'        => $validated['email'],
                    'phone_number' => $validated['phone_number'],
                    'matra'        => $validated['matra'] ?? $user->personel->matra,
                ]);
            }

            DB::commit();

            \App\Models\AuditLog::record('UPDATE', 'User', $user->id, null, [
                'full_name'   => $validated['full_name'],
                'role'        => $validated['role'],
                'jabatan_pju' => $validated['jabatan_pju'],
            ]);

            return back()->with('success', "Akun PJU {$validated['full_name']} berhasil dimutakhirkan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memperbarui akun PJU: ' . $e->getMessage()]);
        }
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);
        $name = $user->personel ? $user->personel->full_name : $user->username;

        DB::beginTransaction();
        try {
            if ($user->personel) {
                $user->personel->delete();
            }
            $user->delete();

            DB::commit();

            \App\Models\AuditLog::record('DELETE', 'User', $id, null, ['name' => $name]);

            return back()->with('success', "Akun PJU {$name} berhasil dihapus dari sistem.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus akun PJU: ' . $e->getMessage()]);
        }
    }
}
