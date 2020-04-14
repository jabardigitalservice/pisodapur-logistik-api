<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Agency;

class AgencyController extends Controller
{
    public function store(Request $request)
    {
        $model = new Agency();
        $model->fill($request->input());
        if ($model->save()) {
            return $model;
        }
    }
}
