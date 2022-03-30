<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Requests\GetLeaderRequest;
use App\Models\Leader;
use App\Http\Controllers\Controller;
use App\Http\Resources\LeaderResource;

class LeaderController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Leader  $leader
     * @return \Illuminate\Http\Response
     */
    public function __invoke(GetLeaderRequest $request)
    {
        $data = Leader::where('phase', $request->phase)->first();
        return new LeaderResource($data);
    }
}
