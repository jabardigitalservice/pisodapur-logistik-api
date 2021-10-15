<?php

namespace App;

use App\Enums\AllocationRequestTypeEnum;
use Illuminate\Database\Eloquent\Model;

class AllocationRequest extends Model
{
    protected $fillable = [
        'letter_number',
        'letter_date',
        'type',
        'applicant_name',
        'applicant_position',
        'applicant_agency_id',
        'applicant_agency_name',
        'distribution_description',
        'letter_url',
    ];

    protected $appends = ['allocation_material_requests_total'];

    public function getAllocationMaterialRequestsTotalAttribute($value)
    {
        $totalQtyMaterialRequested = AllocationMaterialRequest::where('allocation_request_id', $this->id)->sum('qty');
        return $totalQtyMaterialRequested;
    }

    public function allocationDistributionRequests()
    {
        return $this->hasMany('App\AllocationDistributionRequest');
    }

    public function allocationMaterials()
    {
        return $this->hasMany('App\AllocationMaterial');
    }

    public function allocationMaterialRequests()
    {
        return $this->hasMany('App\AllocationMaterialRequest');
    }

    public function scopeAlkes($query)
    {
        return $query->where('type', AllocationRequestTypeEnum::alkes());
    }

    public function scopeVaccine($query)
    {
        return $query->where('type', AllocationRequestTypeEnum::vaccine());
    }

    public function scopeFilter($query, $request)
    {
        $isHasDateRangeFilter = $request->has('start_date') && $request->has('end_date');

        $query->when($isHasDateRangeFilter, function ($query) use ($request) {
            $query->whereBetween('letter_date', [$request->input('start_date'), $request->input('end_date')]);
        })
        ->when($request->has('search'), function ($query) use ($request) {
            $query->where('letter_number', 'LIKE', '%' . $request->input('search') . '%');
        })
        ->when($request->has('status'), function ($query) use ($request) {
            $query->where('status',  $request->input('status'));
        });

        return $query;
    }
}
