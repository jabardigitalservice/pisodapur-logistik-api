<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    protected $table = 'fileuploads';
    
    protected $fillable = [
        'id', 'name', 'created_at', 'updated_at'
    ];

}
