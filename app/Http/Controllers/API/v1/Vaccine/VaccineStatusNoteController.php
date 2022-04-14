<?php

namespace App\Http\Controllers\API\v1\Vaccine;

use App\Http\Controllers\Controller;
use App\Models\Vaccine\VaccineStatusNote;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VaccineStatusNoteController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = VaccineStatusNote::get();
        return response()->format(Response::HTTP_OK, 'success', $data);
    }
}
