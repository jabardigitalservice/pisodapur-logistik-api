<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Letter;
use App\Applicant;
use DB;

class FileUpload extends Model
{
    protected $table = 'fileuploads';

    const LETTER_PATH = 'registration/letter';
    const APPLICANT_IDENTITY_PATH = 'registration/applicant_identity';
    const DISK = 's3';

    protected $fillable = [
        'id', 'name', 'created_at', 'updated_at'
    ];

    static function storeLetterFile($request)
    {
        $fileuploadid = null;
        $path = storage::disk(self::DISK)->put(self::LETTER_PATH, $request->letter_file);
        $fileupload = self::create(['name' => $path]);
        $fileuploadid = $fileupload->id;
        $request->request->add(['agency_id' => $request->id]);
        $request->request->add(['letter' => $fileuploadid]);
        $deleteotherletter = letter::where('agency_id', '=', $request->agency_id)->delete();
        $letter = letter::create($request->all());
        $letter->file_path = storage::disk(self::DISK)->url($fileupload->name);
        return $letter;
    }

    static function storeApplicantFile($request)
    {
        $path = Storage::disk(self::DISK)->put(self::APPLICANT_IDENTITY_PATH, $request->applicant_file);
        $fileUpload = self::create(['name' => $path]);
        $fileUploadId = $fileUpload->id;
        
        $applicant = Applicant::where('id', '=', $request->applicant_id)->update(['file' => $fileUploadId]);
        return $fileUpload;
    }
}
