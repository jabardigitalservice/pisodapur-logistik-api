<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuthKey extends Model
{
    protected $fillable = ['name', 'token'];
}
