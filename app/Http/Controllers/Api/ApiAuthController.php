<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ApiAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah.'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda dinonaktifkan oleh administrator.'
            ], 403);
        }

        $token = $user->createToken('flutter_auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role?->name,
                'personel' => $user->personel ? [
                    'id' => $user->personel->id,
                    'full_name' => $user->personel->full_name,
                    'nikc' => $user->personel->nikc,
                    'pangkat' => $user->personel->pangkat,
                    'matra' => $user->personel->matra,
                    'angkatan' => $user->personel->angkatan,
                    'is_asn' => $user->personel->is_asn,
                ] : null
            ]
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role?->name,
                'personel' => $user->personel ? [
                    'id' => $user->personel->id,
                    'full_name' => $user->personel->full_name,
                    'nikc' => $user->personel->nikc,
                    'pangkat' => $user->personel->pangkat,
                    'matra' => $user->personel->matra,
                    'angkatan' => $user->personel->angkatan,
                    'is_asn' => $user->personel->is_asn,
                    'phone_number' => $user->personel->phone_number,
                    'address' => $user->personel->address,
                    'province' => $user->personel->province,
                    'city' => $user->personel->city,
                    'district' => $user->personel->district,
                    'village' => $user->personel->village,
                    'postal_code' => $user->personel->postal_code,
                    'photo_profile' => $user->personel->photo_profile ? asset('storage/' . $user->personel->photo_profile) : null,
                ] : null
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout dari sistem.'
        ]);
    }
}
