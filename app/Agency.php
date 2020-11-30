<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Agency extends Model
{
    protected $table = 'agency';

    protected $fillable = [
        'master_faskes_id',
        'agency_type',
        'agency_name',
        'phone_number',
        'location_district_code',
        'location_subdistrict_code',
        'location_village_code',
        'location_address'
    ];

    static function getList($request, $defaultOnly)
    {
        try {
            $data = self::selectRaw('*, 0 as completeness, 0 as is_reference');
            $data = self::getDefaultWith($data);

            if (!$defaultOnly) {
                $data = self::withLogisticRequestData($data);
                $data = self::withRecommendationItems($data);
                $data = self::whereHasApplicantData($data, $request);
                $data = self::whereHasApplicantFilterByStatusData($data, $request);
                $data = self::whereData($data, $request);
            }
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }
        return $data;
    }
    
    static function getDefaultWith($data)
    {
        return $data->with([    
            'masterFaskes',            
            'masterFaskesType',            
            'city',
            'subDistrict',
            'village',
            'applicant' => function ($query) {
                $query->select([ 'id', 'agency_id', 'applicant_name', 'applicants_office', 'file', 'email', 'primary_phone_number', 'secondary_phone_number', 'verification_status', 'note', 'approval_status', 'approval_note', 'stock_checking_status', 'application_letter_number', 'verified_by', 'verified_at', 'approved_by', 'approved_at', DB::raw('concat(approval_status, "-", verification_status) as status'), DB::raw('concat(approval_status, "-", verification_status) as statusDetail'), 'finalized_by', 'finalized_at', 'is_urgency' ]);
                $query->where('is_deleted', '!=' , 1);
                $query->with([
                    'letter',
                    'verifiedBy' => function ($query) {
                        return $query->select(['id', 'name', 'agency_name', 'handphone']);
                    },
                    'approvedBy' => function ($query) {
                        return $query->select(['id', 'name', 'agency_name', 'handphone']);
                    },
                    'finalizedBy' => function ($query) {
                        return $query->select(['id', 'name', 'agency_name', 'handphone']);
                    }
                ]);
            }
        ]);
    }

    static function withLogisticRequestData($data)
    {
        return $data->with([
            'logisticRequestItems' => function ($query) {
                return $query->select(['agency_id', 'product_id', 'brand', 'quantity', 'unit', 'usage', 'priority']);
            },
            'logisticRequestItems.product' => function ($query) {
                return $query->select(['id', 'name', 'material_group_status', 'material_group']);
            },
            'logisticRequestItems.masterUnit' => function ($query) {
                return $query->select(['id', 'unit as name']);
            }
        ]);
    }

    static function withRecommendationItems($data)
    {
        return $data->with([
            'recommendationItems' => function ($query) {
                return $query->whereNotIn('status', [
                    LogisticRealizationItems::STATUS_NOT_AVAILABLE,
                    LogisticRealizationItems::STATUS_NOT_YET_FULFILLED
                ]);
            },
            'finalizationItems' => function ($query) {
                return $query->whereNotIn('final_status', [
                    LogisticRealizationItems::STATUS_NOT_AVAILABLE,
                    LogisticRealizationItems::STATUS_NOT_YET_FULFILLED
                ]);
            }
        ]);
    }

    static function whereHasApplicantData($data, $request)
    {
        return $data->whereHas('applicant', function ($query) use ($request) {
            $query->where('is_deleted', '!=' , 1);

            if ($request->source_data) {
                $query->where('source_data', $request->source_data);
            }

            if ($request->stock_checking_status) {
                $query->where('stock_checking_status', $request->stock_checking_status);
            }

            if ($request->date) {
                $query->whereRaw('DATE(created_at) = ?', [$request->date]);
            }
        });
    }

    static function whereHasApplicantFilterByStatusData($data, $request)
    {
        return $data->whereHas('applicant', function ($query) use ($request) {
            if ($request->is_rejected) {
                $query->where('verification_status', Applicant::STATUS_REJECTED)->orWhere('approval_status', Applicant::STATUS_REJECTED);
            } else {
                if ($request->verification_status) {
                    $query->where('verification_status', $request->verification_status);
                }

                if ($request->approval_status) {
                    $query->where('approval_status', $request->approval_status);
                }
            }
        });
    }

    static function whereData($data, $request)
    {
        return $data->where(function ($query) use ($request) {
            if ($request->agency_name) {
                $query->where('agency_name', 'LIKE', "%{$request->agency_name}%");
            }

            if ($request->city_code) {
                $query->where('location_district_code', $request->city_code);
            }

            if ($request->faskes_type) {
                $query->where('agency_type', $request->faskes_type);
            }
        });
    }

    public function masterFaskesType()
    {
        return $this->hasOne('App\MasterFaskesType', 'id', 'agency_type');
    }

    public function masterFaskes()
    {
        return $this->hasOne('App\MasterFaskes', 'id', 'master_faskes_id');
    }

    public function applicant()
    {
        return $this->hasOne('App\Applicant', 'agency_id', 'id');
    }

    public function letter()
    {
        return $this->hasOne('App\Letter', 'agency_id', 'id');
    }

    public function need()
    {
        return $this->hasMany('App\Needs', 'agency_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo('App\City', 'location_district_code', 'kemendagri_kabupaten_kode');
    }

    public function village()
    {
        return $this->belongsTo('App\Village', 'location_village_code', 'kemendagri_desa_kode');
    }

    public function subDistrict()
    {
        return $this->belongsTo('App\Subdistrict', 'location_subdistrict_code', 'kemendagri_kecamatan_kode');
    }

    public function logisticRequestItems()
    {
        return $this->need();
    }

    public function logisticRealizationItems()
    {
        return $this->hasMany('App\LogisticRealizationItems', 'agency_id', 'id');
    }

    public function recommendationItems()
    {
        return $this->logisticRealizationItems();
    }

    public function finalizationItems()
    {
        return $this->logisticRealizationItems();
    }

    public function tracking()
    {
        return $this->hasOne('App\Applicant', 'agency_id', 'id');
    }
}
