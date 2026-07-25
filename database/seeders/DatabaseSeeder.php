<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $masterProvinsi = [
            ['kode_latsarmil' => '01', 'kode_wilayah' => '11', 'nama' => 'Aceh'],
            ['kode_latsarmil' => '02', 'kode_wilayah' => '12', 'nama' => 'Sumatera Utara'],
            ['kode_latsarmil' => '03', 'kode_wilayah' => '13', 'nama' => 'Sumatera Barat'],
            ['kode_latsarmil' => '04', 'kode_wilayah' => '14', 'nama' => 'Riau'],
            ['kode_latsarmil' => '05', 'kode_wilayah' => '21', 'nama' => 'Kepulauan Riau'],
            ['kode_latsarmil' => '06', 'kode_wilayah' => '15', 'nama' => 'Jambi'],
            ['kode_latsarmil' => '07', 'kode_wilayah' => '17', 'nama' => 'Bengkulu'],
            ['kode_latsarmil' => '08', 'kode_wilayah' => '16', 'nama' => 'Sumatera Selatan'],
            ['kode_latsarmil' => '09', 'kode_wilayah' => '19', 'nama' => 'Kepulauan Bangka Belitung'],
            ['kode_latsarmil' => '10', 'kode_wilayah' => '18', 'nama' => 'Lampung'],
            ['kode_latsarmil' => '11', 'kode_wilayah' => '36', 'nama' => 'Banten'],
            ['kode_latsarmil' => '12', 'kode_wilayah' => '32', 'nama' => 'Jawa Barat'],
            ['kode_latsarmil' => '13', 'kode_wilayah' => '31', 'nama' => 'DKI Jakarta'],
            ['kode_latsarmil' => '14', 'kode_wilayah' => '33', 'nama' => 'Jawa Tengah'],
            ['kode_latsarmil' => '15', 'kode_wilayah' => '34', 'nama' => 'Yogyakarta', 'nama_wilayah' => 'DI Yogyakarta'],
            ['kode_latsarmil' => '16', 'kode_wilayah' => '35', 'nama' => 'Jawa Timur'],
            ['kode_latsarmil' => '17', 'kode_wilayah' => '51', 'nama' => 'Bali'],
            ['kode_latsarmil' => '18', 'kode_wilayah' => '52', 'nama' => 'Nusa Tenggara Barat'],
            ['kode_latsarmil' => '19', 'kode_wilayah' => '53', 'nama' => 'Nusa Tenggara Timur'],
            ['kode_latsarmil' => '20', 'kode_wilayah' => '61', 'nama' => 'Kalimantan Barat'],
            ['kode_latsarmil' => '21', 'kode_wilayah' => '63', 'nama' => 'Kalimantan Selatan'],
            ['kode_latsarmil' => '22', 'kode_wilayah' => '62', 'nama' => 'Kalimantan Tengah'],
            ['kode_latsarmil' => '23', 'kode_wilayah' => '64', 'nama' => 'Kalimantan Timur'],
            ['kode_latsarmil' => '24', 'kode_wilayah' => '65', 'nama' => 'Kalimantan Utara'],
            ['kode_latsarmil' => '25', 'kode_wilayah' => '75', 'nama' => 'Gorontalo'],
            ['kode_latsarmil' => '26', 'kode_wilayah' => '76', 'nama' => 'Sulawesi Barat'],
            ['kode_latsarmil' => '27', 'kode_wilayah' => '73', 'nama' => 'Sulawesi Selatan'],
            ['kode_latsarmil' => '28', 'kode_wilayah' => '72', 'nama' => 'Sulawesi Tengah'],
            ['kode_latsarmil' => '29', 'kode_wilayah' => '74', 'nama' => 'Sulawesi Tenggara'],
            ['kode_latsarmil' => '30', 'kode_wilayah' => '71', 'nama' => 'Sulawesi Utara'],
            ['kode_latsarmil' => '31', 'kode_wilayah' => '81', 'nama' => 'Maluku'],
            ['kode_latsarmil' => '32', 'kode_wilayah' => '82', 'nama' => 'Maluku Utara'],
            ['kode_latsarmil' => '33', 'kode_wilayah' => '91', 'nama' => 'Papua'],
            ['kode_latsarmil' => '34', 'kode_wilayah' => '92', 'nama' => 'Papua Barat'],
        ];

        foreach ($masterProvinsi as $provinsi) {
            DB::table('master_provinsi')->updateOrInsert(
                ['kode_latsarmil' => $provinsi['kode_latsarmil']],
                array_merge($provinsi, [
                    'nama_wilayah' => $provinsi['nama_wilayah'] ?? $provinsi['nama'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $masterKepangkatan = [
            ['urutan' => 1, 'nama' => 'Prada KC', 'kelompok_nikc' => '3', 'klaim_langsung' => true],
            ['urutan' => 2, 'nama' => 'Pratu KC', 'kelompok_nikc' => '3', 'klaim_langsung' => false],
            ['urutan' => 3, 'nama' => 'Praka KC', 'kelompok_nikc' => '3', 'klaim_langsung' => false],
            ['urutan' => 4, 'nama' => 'Kopda KC', 'kelompok_nikc' => '3', 'klaim_langsung' => false],
            ['urutan' => 5, 'nama' => 'Koptu KC', 'kelompok_nikc' => '3', 'klaim_langsung' => false],
            ['urutan' => 6, 'nama' => 'Kopka KC', 'kelompok_nikc' => '3', 'klaim_langsung' => false],
            ['urutan' => 7, 'nama' => 'Serda KC', 'kelompok_nikc' => '2', 'klaim_langsung' => true],
            ['urutan' => 8, 'nama' => 'Sertu KC', 'kelompok_nikc' => '2', 'klaim_langsung' => false],
            ['urutan' => 9, 'nama' => 'Serka KC', 'kelompok_nikc' => '2', 'klaim_langsung' => false],
            ['urutan' => 10, 'nama' => 'Serma KC', 'kelompok_nikc' => '2', 'klaim_langsung' => false],
            ['urutan' => 11, 'nama' => 'Pelda KC', 'kelompok_nikc' => '2', 'klaim_langsung' => false],
            ['urutan' => 12, 'nama' => 'Peltu KC', 'kelompok_nikc' => '2', 'klaim_langsung' => false],
            ['urutan' => 13, 'nama' => 'Letda KC', 'kelompok_nikc' => '1', 'klaim_langsung' => true],
            ['urutan' => 14, 'nama' => 'Lettu KC', 'kelompok_nikc' => '1', 'klaim_langsung' => false],
            ['urutan' => 15, 'nama' => 'Kapten KC', 'kelompok_nikc' => '1', 'klaim_langsung' => false],
            ['urutan' => 16, 'nama' => 'Mayor KC', 'kelompok_nikc' => '1', 'klaim_langsung' => false],
            ['urutan' => 17, 'nama' => 'Letkol KC', 'kelompok_nikc' => '1', 'klaim_langsung' => false],
            ['urutan' => 18, 'nama' => 'Kolonel KC', 'kelompok_nikc' => '1', 'klaim_langsung' => false],
        ];

        foreach ($masterKepangkatan as $pangkat) {
            DB::table('master_kepangkatan')->updateOrInsert(
                ['nama' => $pangkat['nama']],
                array_merge($pangkat, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // 1. Seed Roles
        $adminRoleId = DB::table('roles')->insertGetId([
            'name' => 'admin',
            'display_name' => 'Administrator Sistem',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $komandanRoleId = DB::table('roles')->insertGetId([
            'name' => 'komandan',
            'display_name' => 'Komandan Satuan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $personelRoleId = DB::table('roles')->insertGetId([
            'name' => 'personel',
            'display_name' => 'Anggota Komponen Cadangan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $kordinatorAngkatanRoleId = DB::table('roles')->insertGetId([
            'name' => 'kordinator_angkatan',
            'display_name' => 'Koordinator Angkatan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $kordinatorMatraRoleId = DB::table('roles')->insertGetId([
            'name' => 'kordinator_matra',
            'display_name' => 'Koordinator Matra',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Seed Admin Default User
        DB::table('users')->insert([
            'uuid' => Str::uuid(),
            'role_id' => $adminRoleId,
            'username' => 'admin.sisfopers',
            'email' => 'admin@sisfoperskc.go.id',
            'password' => Hash::make('P@sswordEnterprise2026'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Seed Komandan Default User
        DB::table('users')->insert([
            'uuid' => Str::uuid(),
            'role_id' => $komandanRoleId,
            'username' => 'komandan.kc',
            'email' => 'komandan@sisfoperskc.go.id',
            'password' => Hash::make('KomandanKC2026!'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3a. Seed Koordinator Angkatan Default User & Personel
        $userAngkatanId = DB::table('users')->insertGetId([
            'uuid' => Str::uuid(),
            'role_id' => $kordinatorAngkatanRoleId,
            'username' => 'koordinator.angkatan',
            'email' => 'koordinator.angkatan@sisfoperskc.go.id',
            'password' => Hash::make('KordinatorAngkatan2026!'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('personels')->insert([
            'uuid' => Str::uuid(),
            'user_id' => $userAngkatanId,
            'nik' => '1234567890123456',
            'nikc' => '21000000101199013',
            'full_name' => 'Ahmad Koordinator',
            'pangkat' => 'Serda KC',
            'pob' => 'Jakarta',
            'dob' => '1990-01-01',
            'gender' => 'L',
            'matra' => 'AD',
            'angkatan' => '2026',
            'phone_number' => '081234567890',
            'address' => 'Jl. Kordinator No. 1',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Pusat',
            'district' => 'Gambir',
            'village' => 'Gambir',
            'postal_code' => '10110',
            'status_profile' => 'LENGKAP',
            'face_verified' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3b. Seed Koordinator Matra Default User & Personel
        $userMatraId = DB::table('users')->insertGetId([
            'uuid' => Str::uuid(),
            'role_id' => $kordinatorMatraRoleId,
            'username' => 'koordinator.matra',
            'email' => 'koordinator.matra@sisfoperskc.go.id',
            'password' => Hash::make('KordinatorMatra2026!'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('personels')->insert([
            'uuid' => Str::uuid(),
            'user_id' => $userMatraId,
            'nik' => '1234567890123457',
            'nikc' => '22000000201199116',
            'full_name' => 'Budi Koordinator',
            'pangkat' => 'Serda KC',
            'pob' => 'Surabaya',
            'dob' => '1991-01-01',
            'gender' => 'L',
            'matra' => 'AL',
            'angkatan' => '2026',
            'phone_number' => '081234567891',
            'address' => 'Jl. Kordinator No. 2',
            'province' => 'Jawa Timur',
            'city' => 'Surabaya',
            'district' => 'Gubeng',
            'village' => 'Gubeng',
            'postal_code' => '60281',
            'status_profile' => 'LENGKAP',
            'face_verified' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Seed Default Enterprise Configuration Settings
        $settings = [
            ['key' => 'app_name', 'value' => 'SISFOPERSKC', 'group' => 'general'],
            ['key' => 'app_title', 'value' => 'Sistem Informasi Personel Komponen Cadangan', 'group' => 'general'],
            ['key' => 'footer_text', 'value' => '© 2026 SISFOPERSKC. All Rights Reserved.', 'group' => 'general'],
            ['key' => 'wa_host', 'value' => '127.0.0.1', 'group' => 'whatsapp'],
            ['key' => 'wa_port', 'value' => '3100', 'group' => 'whatsapp'],
            ['key' => 'wa_api_key', 'value' => encrypt('SECRET_INTEGRATION_KEY_PORT_3100'), 'group' => 'whatsapp'],
            ['key' => 'wa_session', 'value' => 'sisfopers_session', 'group' => 'whatsapp'],
            ['key' => 'maintenance_mode', 'value' => '0', 'group' => 'system']
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->insert([
                'key' => $setting['key'],
                'value' => $setting['value'],
                'group' => $setting['group'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
