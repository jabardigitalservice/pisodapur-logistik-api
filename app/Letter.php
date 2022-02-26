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

    public function getLetterAttribute($value)
    {
        $data = FileUpload::find($value);
        if (isset($data->name)) {
            if (substr($data->name, 0, 12) === 'registration') {
                return config('aws.url') . $data->name;
            } else {
                return $data->name;
            }
        } else {
            return '';
        }
    }
}
