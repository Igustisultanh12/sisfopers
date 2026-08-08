<?php

namespace App\Http\Controllers\Personel;

use App\Http\Controllers\Controller;
use App\Helpers\MilitaryUnitHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KewilayahanController extends Controller
{
    public function getUnits(Request $request)
    {
        $matra = $request->query('matra', 'AD');
        return response()->json(MilitaryUnitHelper::getUnits($matra));
    }

    public function updateKewilayahan(Request $request)
    {
        $user = $request->user();
        $personel = $user->getPersonelOrAutoCreate();

        if (!$personel) {
            return back()->withErrors(['error' => 'Data personel tidak ditemukan.']);
        }

        if ($personel->is_kewilayahan_updated && $personel->kotama && $personel->satuan_kewilayahan) {
            return back()->with('info', 'Data kewilayahan Anda sudah terisi dan hanya dapat diperbarui 1 kali.');
        }

        $validated = $request->validate([
            'kotama'             => 'required|string|max:150',
            'satuan_kewilayahan' => 'required|string|max:150',
        ]);

        $personel->update([
            'kotama'                 => $validated['kotama'],
            'satuan_kewilayahan'     => $validated['satuan_kewilayahan'],
            'is_kewilayahan_updated' => true,
        ]);

        $user->unsetRelation('personel');

        \App\Models\AuditLog::record('UPDATE', 'Personel', $personel->id, null, [
            'kotama'             => $validated['kotama'],
            'satuan_kewilayahan' => $validated['satuan_kewilayahan'],
        ]);

        return back()->with('success', 'Data Komando Kewilayahan (' . $validated['kotama'] . ' / ' . $validated['satuan_kewilayahan'] . ') berhasil diperbarui.');
    }
}
