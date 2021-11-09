<?php

namespace App\Http\Controllers\API\v1;

use App\MasterFaskes;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\VaccineRequest\GetMasterFaskesRequest;
use App\Traits\PaginateTrait;
use App\Validation;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class MasterFaskesController extends Controller
{
    use PaginateTrait;

    public function index(GetMasterFaskesRequest $request)
    {
        $limit = $request->input('limit', 20);
        $sort = $this->getValidOrderDirection($request->input('sort'));
        $isPaginated = $request->input('is_paginated', 1);

        $data = MasterFaskes::with('masterFaskesType')
                ->where(function ($query) use ($request) {
                    $query->when($request->has('nama_faskes'), function ($query) use ($request) {
                        $query->where('master_faskes.nama_faskes', 'LIKE', "%{$request->input('nama_faskes')}%");
                    })
                    ->when($request->has('id_tipe_faskes'), function ($query) use ($request) {
                        $query->where('master_faskes.id_tipe_faskes', '=', $request->input('id_tipe_faskes'));
                    })
                    ->when($request->has('is_faskes'), function ($query) use ($request) {
                        $query->when($request->input('is_faskes'), function ($query) use ($request) {
                            $query->whereIn('master_faskes.id_tipe_faskes', [1, 2, 3]);
                        },  function ($query) use ($request) {
                            $query->whereIn('master_faskes.id_tipe_faskes', [4, 5]);
                        });
                    })
                    ->when($request->has('verification_status'), function ($query) use ($request) {
                        $query->where('master_faskes.verification_status', '=', $request->input('verification_status'));
                    }, function ($query) use ($request) {
                        $query->where('master_faskes.verification_status', '=', MasterFaskes::STATUS_VERIFIED);
                    })
                    ->when($request->has('is_imported'), function ($query) use ($request) {
                        $query->where('master_faskes.is_imported', $request->input('is_imported'));
                    });
                })
                ->orderBy('nama_faskes', $sort);

        $data = $isPaginated ? $data->paginate($limit) : $data->select('id', 'nama_faskes')->get();

        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function show($id)
    {
        $data =  MasterFaskes::find($id);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function store(Request $request)
    {
        $model = new MasterFaskes();
        $param = [
            'nomor_izin_sarana' => 'required',
            'nama_faskes' => 'required',
            'id_tipe_faskes' => 'required',
            'nama_atasan' => 'required',
            'point_latitude_longitude' => 'string',
            'permit_file' => 'required|mimes:jpeg,jpg,png|max:10240'
        ];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === Response::HTTP_OK) {
            try {
                $model->fill($request->input());
                $model->verification_status = 'not_verified';
                $model->is_imported = 0;
                $model->permit_file = $this->permitLetterStore($request);
                $model->save();
                $response = response()->format(Response::HTTP_OK, 'success', $model);
            } catch (\Exception $e) {
                $response = response()->format(Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
            }
        }
        return $response;
    }

    public function verify(Request $request, $id)
    {
        $param = ['verification_status' => 'required'];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === Response::HTTP_OK) {
            if ($request->verification_status == 'verified' || $request->verification_status == 'rejected') {
                try {
                    $model =  MasterFaskes::findOrFail($id);
                    $model->verification_status = $request->verification_status;
                    $model->save();
                    $response = response()->format(Response::HTTP_OK, 'success', $model);
                } catch (\Exception $e) {
                    $response = response()->json(array('message' => 'could_not_verify_faskes'), Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
        }
        return $response;
    }

    public function permitLetterStore($request)
    {
        $path = null;
        if ($request->hasFile('permit_file')) {
            $path = Storage::put('registration/letter', $request->permit_file);
        }
        return $path;
    }
}
