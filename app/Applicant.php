<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\FileUpload;
use League\Flysystem\File;

class Applicant extends Model
{
    protected $table = 'applicants';

    const STATUS_NOT_VERIFIED = 'not_verified';
    const STATUS_VERIFIED = 'verified';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

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
        'approval_status',
        'approval_note',
        'approved_by',
        'approved_at',
        'stock_checking_status',
        'application_letter_number'
    ];

    protected $casts = [
        'request' => 'boolean',
        'verification' => 'boolean',
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

    public function city()
    {
        return $this->belongsTo('App\City', 'location_district_code', 'kemendagri_kabupaten_kode');
    }

    public function letter()
    {
        return $this->hasOne('App\Letter', 'agency_id', 'id');
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

    public function getVerificationStatusAttribute($value)
    {
        $status = $value === self::STATUS_NOT_VERIFIED ? 'Belum Terverifikasi' : ($value === self::STATUS_VERIFIED ? 'Terverifikasi' : ($value === self::STATUS_REJECTED ? 'Pengajuan Ditolak' : ''));
        return $status;
    }

    public function getFileAttribute($value)
    {
        $data = FileUpload::find($value);
        if (substr($data->name, 0, 12) === 'registration') {
            return env('AWS_CLOUDFRONT_URL') . $data->name;
        } else {
            return $data->name;
        }
    }

    public function getApprovalStatusAttribute($value)
    {
        $status = $value === self::STATUS_APPROVED ? 'Telah Disetujui' : ($value === self::STATUS_REJECTED ? 'Permohonan Ditolak' : '');
        return $status;
    }

    // Cast for Tracking Module
    public function getApprovalAttribute($value)
    {
        $status = $value === self::STATUS_APPROVED ? TRUE : ($value === self::STATUS_REJECTED ? "reject" : FALSE);
        return $status;
    }

    // Cast for Tracking Module
    public function getStatusAttribute($value)
    {
        $status = 'Permohonan Diterima';
        if ($value == self::STATUS_REJECTED) {
            $status = 'Permohonan Ditolak';
        } elseif ($value == 'verification_' . self::STATUS_REJECTED) {
            $status = 'Administrasi Ditolak';
        } elseif ($value == self::STATUS_APPROVED) {
            $status = 'Permohonan Disetujui';
        } elseif ($value == self::STATUS_VERIFIED) {
            $status = 'Administrasi Terverifikasi';
        } 
        return $status;
    }
}
