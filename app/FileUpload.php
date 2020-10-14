<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Letter;
use DB;

class FileUpload extends Model
{
    protected $table = 'fileuploads';
    
    protected $fillable = [
        'id', 'name', 'created_at', 'updated_at'
    ];

    static function storeData($request)
    {
        DB::beginTransaction();
        try {
            $fileUploadId = null;
            if ($request->hasFile('letter_file')) {
                $path = Storage::disk('s3')->put('registration/letter', $request->letter_file);
                $fileUpload = self::create(['name' => $path]);
                $fileUploadId = $fileUpload->id;
            }
            $request->request->add(['letter' => $fileUploadId]);
            $deleteOtherLetter = Letter::where('agency_id', '=', $request->agency_id)->delete();
            $letter = Letter::create($request->all());
            $letter->file_path = Storage::disk('s3')->url($fileUpload->name);  
            DB::commit();
            $response = response()->format(200, 'success', $letter);
        } catch (\Exception $exception) {
            DB::rollBack();
            $response = response()->format(400, $exception->getMessage());
        }
        return $response;
    }
}
