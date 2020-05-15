<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Validator;
use App\Needs;
use App\Agency;
use App\Applicant;
use App\FileUpload;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\LogisticRequestResource;
use App\Letter;
use DB;
use JWTAuth;
use App\Imports\LogisticRequestImport;
use App\Imports\MultipleSheetImport;
use App\Imports\LogisticImport;
use Maatwebsite\Excel\Facades\Excel;

class LogisticRequestController extends Controller
{
    public function index(Request $request)
    {
        if (JWTAuth::user()->roles != 'dinkesprov') {
            return response()->format(404, 'You cannot access this page', null);
        }

        $limit = $request->filled('limit') ? $request->input('limit') : 20;
        $sort = $request->filled('sort') ? $request->input('sort') : 'asc';

        try {
            $data = Agency::with('masterFaskesType', 'applicant', 'city', 'subDistrict')
                ->whereHas('applicant', function ($query) use ($request) {
                    if ($request->filled('verification_status')) {
                        $query->where('verification_status', '=', $request->input('verification_status'));
                    }

                    if ($request->filled('date')) {
                        $query->whereRaw("DATE(created_at) = '" . $request->input('date') . "'");
                    }
                })
                ->where(function ($query) use ($request) {
                    if ($request->filled('agency_name')) {
                        $query->where('agency_name', 'LIKE', "%{$request->input('agency_name')}%");
                    }

                    if ($request->filled('city_code')) {
                        $query->where('location_district_code', '=', $request->input('city_code'));
                    }
                })
                ->orderBy('created_at', 'desc')
                ->orderBy('agency_name', $sort)
                ->paginate($limit);
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }

        return response()->format(200, 'success', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'master_faskes_id' => 'required|numeric',
                    'agency_type' => 'required|string',
                    'agency_name' => 'required',
                    'phone_number' => 'numeric',
                    'location_district_code' => 'required|string',
                    'location_subdistrict_code' => 'required|string',
                    'location_village_code' => 'required|string',
                    'location_address' => 'required|string',
                    'applicant_name' => 'required|string',
                    'applicants_office' => 'required|string',
                    'applicant_file' => 'required|mimes:jpeg,jpg,png|max:5000',
                    'email' => 'required|email',
                    'primary_phone_number' => 'required|numeric',
                    'secondary_phone_number' => 'required|numeric',
                    'logistic_request' => 'required',
                    'letter_file' => 'required|mimes:jpeg,jpg,png,pdf|max:10240'
                ]
            )
        );

        if ($validator->fails()) {
            return response()->format(422, $validator->errors());
        } else {
            DB::beginTransaction();
            try {
                $agency = $this->agencyStore($request);
                $request->request->add(['agency_id' => $agency->id]);

                $applicant = $this->applicantStore($request);
                $request->request->add(['applicant_id' => $applicant->id]);

                $need = $this->needStore($request);
                $letter = $this->letterStore($request);

                $response = array(
                    'agency' => $agency,
                    'applicant' => $applicant,
                    'need' => $need,
                    'letter' => $letter
                );
                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
                return response()->format(400, $exception->getMessage());
            }
        }

        return response()->format(200, 'success', new LogisticRequestResource($response));
    }

    public function agencyStore($request)
    {
        $agency = Agency::create($request->all());

        return $agency;
    }

    public function applicantStore($request)
    {
        $fileUploadId = null;

        if ($request->hasFile('applicant_file')) {
            $path = Storage::disk('s3')->put('registration/applicant_identity', $request->applicant_file);
            $fileUpload = FileUpload::create(['name' => $path]);
            $fileUploadId = $fileUpload->id;
        }

        $request->request->add(['file' => $fileUploadId, 'verification_status' => Applicant::STATUS_NOT_VERIFIED]);
        $applicant = Applicant::create($request->all());

        $applicant->file_path = Storage::disk('s3')->url($fileUpload->name);

        return $applicant;
    }

    public function needStore($request)
    {
        $response = [];
        foreach (json_decode($request->input('logistic_request'), true) as $key => $value) {
            $need = Needs::create(
                [
                    'agency_id' => $request->input('agency_id'),
                    'applicant_id' => $request->input('applicant_id'),
                    'product_id' => $value['product_id'],
                    'brand' => $value['brand'],
                    'quantity' => $value['quantity'],
                    'unit' => $value['unit'],
                    'usage' => $value['usage'],
                    'priority' => $value['priority']
                ]
            );
            $response[] = $need;
        }

        return $response;
    }

    public function letterStore($request)
    {
        $fileUploadId = null;
        if ($request->hasFile('letter_file')) {
            $path = Storage::disk('s3')->put('registration/letter', $request->letter_file);
            $fileUpload = FileUpload::create(['name' => $path]);
            $fileUploadId = $fileUpload->id;
        }

        $request->request->add(['letter' => $fileUploadId]);
        $letter = Letter::create($request->all());

        $letter->file_path = Storage::disk('s3')->url($fileUpload->name);

        return $letter;
    }

    public function show($id)
    {
        $data = Agency::with([
            'masterFaskesType' => function ($query) {
                return $query->select(['id', 'name']);
            },
            'applicant' => function ($query) {
                return $query->select([
                    'id', 'agency_id', 'applicant_name', 'applicant_name', 'applicants_office', 'file', 'email', 'primary_phone_number', 'secondary_phone_number', 'verification_status'
                ]);
            },
            'letter' => function ($query) {
                return $query->select(['id', 'agency_id', 'letter']);
            },
            'city' => function ($query) {
                return $query->select(['id', 'kemendagri_provinsi_nama', 'kemendagri_kabupaten_kode', 'kemendagri_kabupaten_nama']);
            },
            'subDistrict' => function ($query) {
                return $query->select(['id', 'kemendagri_kecamatan_kode', 'kemendagri_kecamatan_nama']);
            },
            'village' => function ($query) {
                return $query->select(['id', 'kemendagri_desa_kode', 'kemendagri_desa_nama']);
            }
        ])->findOrFail($id);

        return response()->format(200, 'success', $data);
    }

    public function verification(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                ['applicant_id' => 'required|numeric', 'verification_status' => 'required|string']
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {

            $applicant = Applicant::findOrFail($request->applicant_id);
            $applicant->verification_status = $request->verification_status;
            $applicant->save();
        }

        return response()->format(200, 'success', $applicant);
    }

    public function listNeed(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                ['agency_id' => 'required']
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            $limit = $request->filled('limit') ? $request->input('limit') : 10;
            $data = Needs::select(
                'needs.id',
                'needs.agency_id',
                'needs.applicant_id',
                'needs.product_id',
                'needs.item',
                'needs.brand',
                'needs.quantity',
                'needs.unit',
                'needs.usage',
                'needs.priority',
                'needs.created_at',
                'needs.updated_at',
                'logistic_realization_items.need_id',
                'logistic_realization_items.realization_quantity',
                'logistic_realization_items.unit_id',
                'logistic_realization_items.realization_date',
                'logistic_realization_items.status',
                'logistic_realization_items.realization_quantity',
                'logistic_realization_items.created_by',
                'logistic_realization_items.updated_by'
            )
            ->with([
                'product' => function ($query) {
                    return $query->select(['id', 'name']);
                },
                'unit' => function ($query) {
                    return $query->select(['id', 'unit']);
                }
            ])
            ->join('logistic_realization_items', 'logistic_realization_items.need_id', '=', 'needs.id', 'left')
            ->where('needs.agency_id', $request->agency_id)->paginate($limit);
            $data->getCollection()->transform(function ($item, $key) {
                $item->status = !$item->status ? 'not_approved' : $item->status;
                return $item;
            });
        }

        return response()->format(200, 'success', $data);
    }

    public function import(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                [
                    'file' => 'required|mimes:xlsx',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->format(422, $validator->errors());
        } else {
            DB::beginTransaction();
            try {
                $import = new LogisticRequestImport;
                Excel::import($import, request()->file('file'));
                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
                return response()->format(400, $exception->getMessage());
            }
        }

        return response()->format(200, 'success', $import->data);
    }

    public function importLogistic(Request $request)
    {
        $import = new MultipleSheetImport();
        $ts = Excel::import($import, request()->file('file'));
        LogisticImport::import($import);
    }
}
