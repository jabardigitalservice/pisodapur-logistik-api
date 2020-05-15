<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\FileUpload;

class Letter extends Model
{
    protected $table = 'letter';

    protected $fillable = [
        'agency_id',
        'applicant_id',
        'letter'
    ];

    public function agency()
    {
        return $this->belongsToOne('App\Agency', 'id', 'agency_id');
    }

    public function getLetterAttribute($value)
    {
        $data = FileUpload::find($value);
        if (substr($data->name, 0, 12) === 'registration') {
            return env('AWS_CLOUDFRONT_URL') . $data->name;
        } else {
            return $data->name;
        }
    }
}
