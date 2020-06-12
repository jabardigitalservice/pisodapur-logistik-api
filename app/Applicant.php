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
        'verified_by'
    ];

    public function agency()
    {
        return $this->belongsToOne('App\Agency', 'id', 'agency_id');
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
}
