<?php

namespace App;

use App\Enums\LogisticRequestStatusEnum;
use Illuminate\Database\Eloquent\Model;
use DB;

class AgencyFilter extends Model
{
    public function scopeSetOrder($query, $request)
    {
        $isRecommendationPhase = $request->input('verification_status') == Applicant::STATUS_VERIFIED && $request->input('approval_status') == Applicant::STATUS_NOT_APPROVED;
        $isRealizationPhase = $request->input('verification_status') == Applicant::STATUS_VERIFIED && $request->input('approval_status') == Applicant::STATUS_APPROVED;
        $sort = $request->input('sort') ?? 'desc'; //default sort by 'desc'
        return $query
            ->when($isRealizationPhase, function ($query) use ($sort) {
                $query
                    ->orderBy('applicants.approved_at', $sort);
            })
            ->when($isRecommendationPhase, function ($query) use ($sort) {
                $query->orderBy('applicants.verified_at', $sort);
            }, function ($query) use ($sort) {
                $query->orderBy('agency.created_at', $sort);
            });
    }

    public function scopeGetDefaultWith($query)
    {
        return $query->with([
            'masterFaskes', 'masterFaskesType', 'city', 'subDistrict', 'village',
            'applicant' => function ($query) {
                $query->select(['id', 'agency_id', 'applicant_name', 'applicants_office', 'file', 'email', 'primary_phone_number', 'secondary_phone_number', 'verification_status', 'note', 'approval_status', 'approval_note', 'stock_checking_status', 'application_letter_number', 'verified_by', 'verified_at', 'approved_by', 'approved_at', DB::raw('concat(approval_status, "-", verification_status) as status'), DB::raw('concat(approval_status, "-", verification_status) as statusDetail'), 'status as status_request', 'finalized_by', 'finalized_at', 'is_urgency', 'is_integrated'])
                    ->active()
                    ->with('letter')
                    ->with('verifiedBy:id,name,agency_name,handphone')
                    ->with('approvedBy:id,name,agency_name,handphone')
                    ->with('finalizedBy:id,name,agency_name,handphone');
            }
        ]);
    }

    public function scopeWithLogisticRequestData($query)
    {
        return $query->with('logisticRequestItems:agency_id,product_id,brand,quantity,unit,usage,priority')
            ->with('logisticRequestItems.product:id,name,material_group_status,material_group')
            ->with('logisticRequestItems.masterUnit:id,unit as name')
            ->with([
                'recommendationItems' => function ($query) {
                    $query->acceptedStatusOnly('status');
                },
                'finalizationItems' => function ($query) {
                    $query->acceptedStatusOnly('final_status');
                }
            ]);
    }

    public function scopeWhereHasApplicant($query, $request)
    {
        return $query->whereHas('applicant', function ($query) use ($request) {
            $query->active();

            $query->when($request->input('source_data'), function ($query) use ($request) {
                $query->where('source_data', $request->input('source_data'));
            });

            $query->when($request->input('stock_checking_status'), function ($query) use ($request) {
                $query->where('stock_checking_status', $request->input('stock_checking_status'));
            });

            $query->when($request->input('start_date') && $request->input('end_date'), function ($query) use ($request) {
                $query->whereBetween('created_at', [$request->input('start_date'), $request->input('end_date')]);
            });

            $query->when($request->has('is_urgency'), function ($query) use ($request) {
                $query->where('is_urgency', $request->input('is_urgency'));
            });

            $query->when($request->has('is_integrated'), function ($query) use ($request) {
                $query->where('is_integrated', $request->input('is_integrated'));
            });

            $query->when($request->has('finalized_by'), function ($query) use ($request) {
                $query->when($request->input('finalized_by'), function ($query) {
                    $query->final();
                }, function ($query) {
                    $query->whereNull('finalized_by');
                });
            });
        });
    }

    public function scopeFinal($query)
    {
        return $query->whereHas('applicant', function ($query) {
            $query->final()->active();
        });
    }

    /**
     * Search Report Scope function
     *
     * Data search can be done by entering "search" in the form of the agency ID or Applicant Name
     *
     * @param $query
     * @param $request
     * @return $query
     */
    public function scopeSearchReport($query, $request)
    {
        $isHasDateRangeFilter = $request->has('start_date') && $request->has('end_date');

        $query->when($isHasDateRangeFilter, function ($query) use ($request) {
            $startDate = $request->has('start_date') ? $request->input('start_date') . ' 00:00:00' : '2020-01-01 00:00:00';
            $endDate = $request->has('end_date') ? $request->input('end_date') . ' 23:59:59' : date('Y-m-d H:i:s');

            $query->whereBetween('acceptance_reports.date', [$startDate, $endDate]);
        })
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('agency.id', 'LIKE', '%' . $request->input('search') . '%')
                        ->orWhereHas('applicant', function ($query) use ($request) {
                            $query->where('applicant_name', 'LIKE', '%' . $request->input('search') . '%');
                        });
                });
            })
            ->when($request->has('status'), function ($query) use ($request) {
                $isReported = $request->input('status') == AcceptanceReport::STATUS_REPORTED;

                $query->when($isReported, function ($query) use ($request) {
                    $query->has('acceptanceReport');
                }, function ($query) use ($request) {
                    $query->doesntHave('acceptanceReport');
                });
            });

        return $query;
    }

    public function scopeWhereStatusCondition($query, $request)
    {
        return $query->whereHas('applicant', function ($query) use ($request) {
            $query->when($request->input('is_rejected'), function ($query) {
                $query->where('verification_status', Applicant::STATUS_REJECTED)
                    ->orWhere('approval_status', Applicant::STATUS_REJECTED);
            }, function ($query) use ($request) {
                $query->when($request->input('verification_status'), function ($query) use ($request) {
                    $query->where('verification_status', $request->input('verification_status'));
                })
                    ->when($request->input('approval_status'), function ($query) use ($request) {
                        $query->where('approval_status', $request->input('approval_status'));
                    });
            });
        });
    }

    public function scopeWhereStatusRequest($query, $request)
    {
        return $query->whereHas('applicant', function ($query) use ($request) {
            $query->when($request->input('status_request'), function ($query) use ($request) {
                $status = $request->input('status_request');

                if ($status === Applicant::STATUS_REJECTED) {
                    $query->where('verification_status', Applicant::STATUS_REJECTED)
                        ->orWhere('approval_status', Applicant::STATUS_REJECTED);
                } elseif (in_array($status, LogisticRequestStatusEnum::getValues())) {
                    $query->where('status', $status);
                } else {
                    $query->whereRaw('concat(approval_status, "-", verification_status) = ?', $status);
                }
            });
        });
    }

    public function scopeWhereHasFaskes($query, $request)
    {
        return $query->whereHas('masterFaskes', function ($query) use ($request) {
            $query->when($request->has('is_reference'), function ($query) use ($request) {
                $query->where('is_reference', '=', $request->is_reference);
            });
        });
    }

    public function scopeWhereHasAgency($query, $request)
    {
        return $query->where(function ($query) use ($request) {
            $query
                ->when($request->input('search'), function ($query) use ($request) {
                    $query->where('agency_name', 'LIKE', "%{$request->input('search')}%")
                        ->orWhereHas('applicant', function ($query) use ($request) {
                            $query->where('applicant_name', 'LIKE', "%{$request->input('search')}%");
                        });
                })
                ->when($request->input('city_code'), function ($query) use ($request) {
                    $query->where('location_district_code', $request->input('city_code'));
                })
                ->when($request->input('faskes_type'), function ($query) use ($request) {
                    $query->where('agency_type', $request->input('faskes_type'));
                })
                ->when($request->has('completeness'), function ($query) use ($request) {
                    $query->where('completeness', $request->input('completeness'));
                });
        });
    }
}
