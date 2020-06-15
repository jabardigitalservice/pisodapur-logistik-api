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
        $limit = $request->filled('limit') ? $request->input('limit') : 20;
        $sort = $request->filled('sort') ? $request->input('sort') : 'asc';

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
                    'nomor_telepon',
                    'nama_atasan',
                    'nomor_registrasi',
                    'nomor_izin_sarana',
                    'verification_status',
                    'latitude',
                    'longitude',
                    'is_imported',
                    'point_latitude_longitude'
                )
                ->where(function ($query) use ($request) {
                    if ($request->filled('nama_faskes')) {
                        $query->where('master_faskes.nama_faskes', 'LIKE', "%{$request->input('nama_faskes')}%");
                    }

                    if ($request->filled('id_tipe_faskes')) {
                        $query->where('master_faskes.id_tipe_faskes', '=', $request->input('id_tipe_faskes'));
                    }

                    if ($request->filled('verification_status')) {
                        $query->where('master_faskes.verification_status', '=', $request->input('verification_status'));
                    } else {
                        $query->where('master_faskes.verification_status', '=', MasterFaskes::STATUS_VERIFIED);
                    }

                    if ($request->filled('is_imported')) {
                        $query->where('master_faskes.is_imported', $request->input('is_imported'));
                    }

                })
                ->orderBy('nama_faskes', $sort)
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
                'nomor_izin_sarana' => 'required',
                'nama_faskes' => 'required',
                'id_tipe_faskes' => 'required',
                'nama_atasan' => 'required',
                'point_latitude_longitude' => 'string'
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
            } else {
                $model = new MasterFaskes();
                $model->fill($request->input());
                $model->verification_status = 'not_verified';
                $model->is_imported = 0;
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
            } elseif ($request->verification_status == 'verified' || $request->verification_status == 'rejected') {
                $model =  MasterFaskes::findOrFail($id);
                $model->verification_status = $request->verification_status;
                if ($model->save()) {
                    return response()->format(200, 'success', $model);
                } else {
                    return response()->json(array('message' => 'could_not_update_faskes'), 500);
                }
            } else {
                return response()->json(['status' => 'fail', 'message' => 'verification_status_value_is_not_accepted']);
            }
        } catch (\Exception $e) {
            return response()->json(array('message' => 'could_not_verify_faskes'), 500);
        }
    }
}
