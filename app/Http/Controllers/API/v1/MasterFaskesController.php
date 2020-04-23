<?php

namespace App\Http\Controllers\API\v1;

use App\MasterFaskes;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

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

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nomor_registrasi' => 'required',
                'nama_faskes' => 'required',
                'id_tipe_faskes' => 'required',
                'nama_atasan' => 'required',
                'longitude' => 'required',
                'latitude' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
            } else {
                $model = new MasterFaskes();
                $model->fill($request->input());
                $model->verification_status = 'not_verified';
                if ($model->save()) {
                    return response()->format(200, 'success', $model);
                }
            }
        } catch (\Exception $e) {
            return response()->json(array('message' => 'could_not_create_faskes'), 500);
        }
    }
    public function verify(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'verification_status' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
            } else {
                $model =  MasterFaskes::findOrFail($id);
                $model->verification_status = 'verified';
                if ($model->save()) {
                    return response()->format(200, 'success', $model);
                } else {
                    return response()->json(array('message' => 'could_not_update_faskes'), 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json(array('message' => 'could_not_verify_faskes'), 500);
        }
        $model = MasterFaskes::findOrFail($id);
        $model->fill($request->input());
        if ($model->save()) return $model;
    }
}
