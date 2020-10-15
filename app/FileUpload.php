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
        DB::beginTransaction();
        try {
            $fileUploadId = null;
            $path = Storage::disk(self::DISK)->put(self::LETTER_PATH, $request->letter_file);
            $fileUpload = self::create(['name' => $path]);
            $fileUploadId = $fileUpload->id;

            $request->request->add(['letter' => $fileUploadId]);
            $deleteOtherLetter = Letter::where('agency_id', '=', $request->agency_id)->delete();
            $letter = Letter::create($request->all());
            $letter->file_path = Storage::disk(self::DISK)->url($fileUpload->name);
            DB::commit();
            $response = response()->format(200, 'success', $letter);
        } catch (\Exception $exception) {
            DB::rollBack();
            $response = response()->format(400, $exception->getMessage());
        }
        return $response;
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
