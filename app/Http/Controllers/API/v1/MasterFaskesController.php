<?php

namespace App\Http\Controllers\API\v1;

use App\MasterFaskes;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MasterFaskesController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->filled('limit') ? $request->filled('limit') : 10;

        $conditions = [];
        if ($request->filled('nama_faskes')) {
            $conditions[] = ['master_faskes.nama_faskes', 'LIKE', "%{$request->input('nama_faskes')}%"];
        }

        if ($request->filled('id_tipe_faskes')) {
            $conditions[] = ['master_faskes.id_tipe_faskes', '=', $request->input('id_tipe_faskes')];
        }

        try {
            $data = MasterFaskes::with('masterFaskesType')
                ->select(
                    'id',
                    'id_tipe_faskes',
                    'nama_faskes',
                    'kode_kab_bps',
                    'kode_kec_bps',
                    'kode_kel_bps',
                    'kode_kab_kemendagri',
                    'kode_kec_kemendagri',
                    'kode_kel_kemendagri',
                    'nama_kab',
                    'nama_kec',
                    'nama_kel',
                    'alamat',
                    'nomor_telepon'
                )
                ->where($conditions)
                ->paginate($limit);
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }

        return response()->format(200, 'success', $data);
    }

    public function show($id)
    {
        try {
            $data =  MasterFaskes::findOrFail($id);
            return response()->format(200, 'success', $data);
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }
    }
}
