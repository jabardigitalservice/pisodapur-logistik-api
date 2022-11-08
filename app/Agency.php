<?php

namespace App;

class Agency extends AgencyFilter
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
        'location_address',
        'total_covid_patients',
        'total_isolation_room',
        'total_bedroom',
        'total_health_worker'
    ];

    protected $appends = ['total_qty', 'type_item_count', 'status_request'];

    public function getTotalQtyAttribute()
    {
        return $this->logisticRealizationItems()
            ->acceptedStatusOnly('final_status')
            ->sum('final_quantity');
    }

    public function getTypeItemCountAttribute()
    {
        return $this->logisticRealizationItems()
            ->acceptedStatusOnly('final_status')
            ->count('material_group');
    }

    public function getStatusRequestAttribute()
    {
        $status =  $this->applicant->statusDetail;

        if (in_array($status, [
            Applicant::STATUS_REJECTED . '-' . Applicant::STATUS_VERIFIED,
            Applicant::STATUS_NOT_APPROVED . '-' . Applicant::STATUS_REJECTED,
        ]) && !$this->applicant->status_request) {
            $status = Applicant::STATUS_REJECTED;
        } elseif ($this->applicant->status_request) {
            $status =  $this->applicant->status_request;
        }

        return $status;
    }

    static function getList($request, $defaultOnly)
    {
        $data = self::select('agency.id', 'master_faskes_id', 'agency_type', 'agency_name', 'phone_number', 'location_district_code', 'location_subdistrict_code', 'location_village_code', 'location_address', 'location_district_code', 'completeness', 'master_faskes.is_reference', 'agency.created_at', 'agency.updated_at', 'total_covid_patients', 'total_isolation_room', 'total_bedroom', 'total_health_worker')
            ->getDefaultWith()
            ->leftJoin('applicants', 'agency.id', '=', 'applicants.agency_id')
            ->leftJoin('master_faskes', 'agency.master_faskes_id', '=', 'master_faskes.id');

        if (!$defaultOnly) {
            $data->withLogisticRequestData()
                ->whereHasApplicant($request)
                ->whereStatusCondition($request)
                ->whereHasFaskes($request)
                ->whereStatusRequest($request)
                ->whereHasAgency($request);
        }

        return $data->setOrder($request);
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

    public function AcceptanceReport()
    {
        return $this->hasOne('App\AcceptanceReport', 'agency_id', 'id');
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
}
