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

    protected $appends = ['application_letter_number'];
    protected $hidden = ['applicant'];

    public function getLetterAttribute($value)
    {
        $letter = '';
        $data = FileUpload::find($value);
        if (isset($data->name)) {
            $letter = $data->name;
            if (substr($data->name, 0, 12) === 'registration') {
                $letter = config('aws.url') . $data->name;
            }
        }
        return $letter;
    }

    public function getApplicationLetterNumberAttribute()
    {
        return $this->applicant->application_letter_number ?? null;
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'id');
    }
}
