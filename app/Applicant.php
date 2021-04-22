<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\FileUpload;
use League\Flysystem\File;

class Applicant extends Model
{
    protected $table = 'applicants';

    const STATUS_NOT_VERIFIED = 'not_verified';
    const STATUS_NOT_APPROVED = 'not_approved';
    const STATUS_VERIFIED = 'verified';
    const STATUS_APPROVED = 'approved';
    const STATUS_FINALIZED = 'finalized';
    const STATUS_REJECTED = 'rejected';

    /**
    * All of the relationships to be touched.
    *
    * @var array
    */
    protected $touches = ['agency'];

    protected $fillable = [
        'agency_id',
        'applicant_name',
        'applicants_office',
        'file',
        'email',
        'primary_phone_number',
        'secondary_phone_number',
        'verification_status',
        'source_data',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'verified_by',
        'verified_at',
        'note',
        'is_urgency',
        'approval_status',
        'approval_note',
        'approved_by',
        'approved_at',
        'stock_checking_status',
        'application_letter_number',
        'finalized_by',
        'finalized_at',
        'is_integrated'
    ];

    protected $casts = [
        'request' => 'boolean',
        'delivering' => 'boolean',
        'delivered' => 'boolean'
    ];

    public function masterFaskesType()
    {
        return $this->hasOne('App\MasterFaskesType', 'id', 'agency_type');
    }

    public function agency()
    {
        return $this->belongsTo('App\Agency', 'agency_id', 'id');
    }

    public function letter()
    {
        return $this->hasOne('App\Letter', 'applicant_id', 'id');
    }

    public function village()
    {
        return $this->belongsTo('App\Village', 'location_village_code', 'kemendagri_desa_kode');
    }

    public function subDistrict()
    {
        return $this->belongsTo('App\Subdistrict', 'location_subdistrict_code', 'kemendagri_kecamatan_kode');
    }

    public function verifiedBy()
    {
        return $this->hasOne('App\User', 'id', 'verified_by');
    }

    public function approvedBy()
    {
        return $this->hasOne('App\User', 'id', 'approved_by');
    }

    public function finalizedBy()
    {
        return $this->hasOne('App\User', 'id', 'finalized_by');
    }

    public function getVerificationStatusAttribute($value)
    {
        $status = $value === self::STATUS_NOT_VERIFIED ? 'Belum Terverifikasi' : ($value === self::STATUS_VERIFIED ? 'Terverifikasi' : ($value === self::STATUS_REJECTED ? 'Pengajuan Ditolak' : ''));
        return $status;
    }

    public function getFileAttribute($value)
    {
        $fileUrl = '';
        $data = FileUpload::find($value);
        if (isset($data->name)) {
            $fileUrl = substr($data->name, 0, 12) === 'registration' ? config('aws.url') . $data->name : $data->name;
        }
        return $fileUrl;
    }

    public function getApprovalStatusAttribute($value)
    {
        $status = $value === self::STATUS_APPROVED ? 'Telah Disetujui' : ($value === self::STATUS_REJECTED ? 'Permohonan Ditolak' : '');
        return $status;
    }

    // Cast for Tracking Module
    public function getVerificationAttribute($value)
    {
        $status = $value === self::STATUS_VERIFIED ? TRUE : ($value === self::STATUS_REJECTED ? TRUE : FALSE);
        $result = [
            'status' => $status,
            'is_reject' => $value === self::STATUS_REJECTED ? TRUE : FALSE,
        ];
        return $result;
    }

    // Cast for Tracking Module
    public function getApprovalAttribute($value)
    {
        $status = $value === self::STATUS_APPROVED ? TRUE : ($value === self::STATUS_REJECTED ? TRUE : FALSE);
        $result = [
            'status' => $status,
            'is_reject' => $value === self::STATUS_REJECTED ? TRUE : FALSE,
        ];
        return $result;
    }

    // Cast for Tracking Module
    public function getStatusAttribute($value)
    {
        $status = 'Permohonan Diterima';
        if ($value == self::STATUS_APPROVED . '-' . self::STATUS_VERIFIED) {
                $status = 'Permohonan Disetujui';
        } elseif ($value == self::STATUS_REJECTED . '-' . self::STATUS_VERIFIED) {
                $status = 'Permohonan Ditolak';
        } elseif ($value == self::STATUS_NOT_APPROVED . '-' . self::STATUS_VERIFIED) {
                $status = 'Administrasi Terverifikasi';
        } elseif ($value == self::STATUS_NOT_APPROVED . '-' . self::STATUS_REJECTED) {
                $status = 'Administrasi Ditolak';
        }
        return $status;
    }

    // Cast for incoming_mail_status attribute
    public function getIncomingMailStatusAttribute($value)
    {
        return $value ? 'Ada Surat Perintah' : 'Belum Ada Surat Perintah';
    }

    static function applicantStore($request)
    {
        $request['verification_status'] = self::STATUS_NOT_VERIFIED;
        $request['applicants_office'] = $request->input('applicants_office') == 'undefined' ? '' : $request->input('applicants_office', '');
        $request['email'] = $request->input('email') == 'undefined' ? '' : $request->input('email', '');
        $request['secondary_phone_number'] = $request->input('secondary_phone_number') == 'undefined' ? '' : $request->input('secondary_phone_number', '');
        $applicant = self::create($request->all());
        return $applicant;
    }

    static function updateApplicant($request, $dataUpdate)
    {
        try {
            $applicant = Applicant::where('id', $request->applicant_id)->where('agency_id', $request->agency_id)->where('is_deleted', '!=' , 1)->firstOrFail();
            $applicant->fill($dataUpdate);
            $applicant->save();
        } catch (\Exception $exception) {
            return response()->format(400, $exception->getMessage());
        }
        return $applicant;
    }

    static function undoStep($request)
    {
        $dataUpdate = [];
        switch ($request->step) {
            case 'final':
                $dataUpdate = self::setNotYetFinalized($dataUpdate);
                $request['status'] = 'realisasi';
                break;
            case 'realisasi':
                $dataUpdate = self::setNotYetApproved($dataUpdate);
                $request['status'] = 'rekomendasi';
                break;
            case 'ditolak rekomendasi':
                $dataUpdate = self::setNotYetApproved($dataUpdate);
                $request['status'] = 'rekomendasi';
                break;
            default:
                $dataUpdate = self::setNotYetVerified($dataUpdate);
                $request['status'] = 'surat';
                break;
        }
        $update = self::updateApplicant($request, $dataUpdate);
        return $request;
    }

    static function setNotYetFinalized($model)
    {
        $model['finalized_by'] = null;
        $model['finalized_at'] = null;
        return $model;
    }

    static function setNotYetApproved($model)
    {
        $model['approval_status'] = 'not_approved';
        $model['approved_by'] = null;
        $model['approved_at'] = null;
        $model['approval_note'] = null;
        $model = self::setNotYetFinalized($model);
        return $model;
    }

    static function setNotYetVerified($model)
    {
        $model['verification_status'] = 'not_verified';
        $model['verified_by'] = null;
        $model['verified_at'] = null;
        $model['note'] = null;
        $model = self::setNotYetApproved($model);
        $model = self::setNotYetFinalized($model);
        return $model;
    }

    public function scopeActive($query)
    {
        return $query->where('is_deleted', '!=' , 1);
    }

    public function scopecreatedBetween($query, $createdAt)
    {
        return $query->whereBetween('created_at', $createdAt);
    }

    public function scopeSourceData($query, $value)
    {
        return $query->where('source_data', $value);
    }

    public function scopeUnverified($query)
    {
        return $query->where('approval_status', Applicant::STATUS_NOT_APPROVED)->where('verification_status', Applicant::STATUS_NOT_VERIFIED);
    }

    public function scopeVerified($query)
    {
        return $query->where('approval_status', Applicant::STATUS_NOT_APPROVED)->where('verification_status', Applicant::STATUS_VERIFIED);
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', Applicant::STATUS_APPROVED)->where('verification_status', Applicant::STATUS_VERIFIED)->whereNull('finalized_by');
    }

    public function scopeFinal($query)
    {
        return $query->whereNotNull('finalized_by');
    }

    public function scopeVerificationRejected($query)
    {
        return $query->where('approval_status', Applicant::STATUS_NOT_APPROVED)->where('verification_status', Applicant::STATUS_REJECTED);
    }

    public function scopeApprovalRejected($query)
    {
        return $query->where('approval_status', Applicant::STATUS_REJECTED)->where('verification_status', Applicant::STATUS_VERIFIED);
    }

    static function requestSummaryResult($params)
    {
        $params['totalRejected'] = $params['totalVerificationRejected'] + $params['totalApprovalRejected'];
        $params['total'] = $params['totalUnverified'] + $params['totalVerified'] + $params['totalApproved'] + $params['totalFinal'] + $params['totalRejected'];

        $data = [
            'total_request' => $params['total'],
            'total_approved' => $params['totalApproved'],
            'total_final' => $params['totalFinal'],
            'total_unverified' => $params['totalUnverified'],
            'total_verified' => $params['totalVerified'],
            'total_rejected' => $params['totalRejected'],
            'total_approval_rejected' => $params['totalApprovalRejected'],
            'total_verification_rejected' => $params['totalVerificationRejected'],
            'total_pikobar' => $params['totalPikobar'],
            'total_dinkesprov' => $params['totalDinkesprov'],
            'last_update' => $params['lastUpdate'] ? date('Y-m-d H:i:s', strtotime($params['lastUpdate']->updated_at)) : '2020-01-01 00:00:00'
        ];
        return $data;
    }

    public function scopeFilter($query, $request)
    {
        return $query->whereHas('agency', function($query) use ($request) {
            if ($request->has('city_code')) {
                $query->where('location_district_code', $request->input('city_code'));
            }
        });
    }
}
