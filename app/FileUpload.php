<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Letter;

class FileUpload extends Model
{
    protected $table = 'fileuploads';

    const LETTER_PATH = 'registration/letter';
    const APPLICANT_IDENTITY_PATH = 'registration/applicant_identity';
    const ACCEPTANCE_REPORT_PATH = 'registration/acceptance_report';
    const DISK = 's3';

    protected $fillable = [
        'id', 'name', 'created_at', 'updated_at'
    ];

    static function storeLetterFile($request)
    {
        $fileuploadid = null;
        $path = Storage::disk(self::DISK)->put(self::LETTER_PATH, $request->letter_file);
        $fileupload = self::create(['name' => $path]);
        $fileuploadid = $fileupload->id;
        $request->request->add(['letter' => $fileuploadid]);
        $deleteotherletter = Letter::where('agency_id', '=', $request->agency_id)->where('applicant_id', '=', $request->applicant_id)->delete();
        $letter = Letter::create($request->all());
        $letter->file_path = Storage::disk(self::DISK)->url($fileupload->name);
        return $letter;
    }

    static function storeApplicantFile($request)
    {
        $path = Storage::disk(self::DISK)->put(self::APPLICANT_IDENTITY_PATH, $request->applicant_file);
        $fileUpload = self::create(['name' => $path]);
        $fileUploadId = $fileUpload->id;
        return $fileUpload;
    }

    static function uploadAcceptanceReportFile($request, $paramName)
    {
        $path = Storage::disk(self::DISK)->put(self::ACCEPTANCE_REPORT_PATH, $request->input($paramName));
        $fileUpload = FileUpload::create(['name' => $path]);
        return $fileUpload;
    }
}
