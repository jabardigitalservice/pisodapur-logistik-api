<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\MasterFaskesVerificationStatusEnum;
use App\MasterFaskes;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\MasterFaskes\StoreMasterFaskesRequest;
use App\Http\Requests\VaccineRequest\GetMasterFaskesRequest;
use App\MasterFaskesType;
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

        $data = MasterFaskes::with(['masterFaskesType', 'village'])
                ->where(function ($query) use ($request) {
                    $query->when($request->has('nama_faskes'), function ($query) use ($request) {
                        $query->where('master_faskes.nama_faskes', 'LIKE', "%{$request->input('nama_faskes')}%");
                    })
                    ->when($request->has('id_tipe_faskes'), function ($query) use ($request) {
                        $query->where('master_faskes.id_tipe_faskes', '=', $request->input('id_tipe_faskes'));
                    })
                    ->when($request->has('is_faskes'), function ($query) use ($request) {
                        $query->when($request->input('is_faskes'), function ($query) use ($request) {
                            $query->whereIn('master_faskes.id_tipe_faskes', MasterFaskesType::HEALTH_FACILITY);
                        },  function ($query) use ($request) {
                            $query->whereIn('master_faskes.id_tipe_faskes', MasterFaskesType::NON_HEALTH_FACILITY);
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

        $data = $isPaginated ? $data->paginate($limit) : $data->select('id', 'nama_faskes', 'id_tipe_faskes', 'kode_kel_kemendagri')->get();

        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function show($id)
    {
        $data =  MasterFaskes::find($id);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function store(StoreMasterFaskesRequest $request)
    {
        $model = new MasterFaskes();
        $model->fill($request->validated());
        $model->verification_status = $request->input('verification_status', MasterFaskesVerificationStatusEnum::not_verified());
        $model->is_imported = 0;
        $model->permit_file = $this->permitLetterStore($request);
        $model->save();
        return response()->format(Response::HTTP_OK, 'success');
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
