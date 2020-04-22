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
        return env('AWS_CLOUDFRONT_URL') . $data->name;
    }
}
