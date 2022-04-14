<?php

namespace App\Models\Vaccine;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class VaccineRequestStatusNote extends Model
{
    use SoftDeletes;

    protected $with = ['vaccineStatusNote:id,name'];

    protected $fillable = ['vaccine_request_id', 'status', 'vaccine_status_note_id', 'note'];

    public function vaccineStatusNote()
    {
        return $this->hasOne('App\Models\Vaccine\VaccineStatusNote', 'id', 'vaccine_status_note_id');
    }

    public static function insertData(Request $request, $vaccine_request_id)
    {
        $vaccineRequestStatusNote = [];
        foreach ($request->input('vaccine_status_note', []) as $note) {
            $vaccineRequestStatusNote[] = [
                'vaccine_request_id' => $vaccine_request_id,
                'status' => $request->status,
                'vaccine_status_note_id' => $note['id'],
                'vaccine_status_note_name' => $note['name'] ?? '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }

        if ($vaccineRequestStatusNote) {
            VaccineRequestStatusNote::where([
                'vaccine_request_id' => $vaccine_request_id,
                'status' => $request->status,
            ])->delete();
            VaccineRequestStatusNote::insert($vaccineRequestStatusNote);
        }
    }
}
