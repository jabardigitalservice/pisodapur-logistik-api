<?php

namespace App\Http\Controllers\API\v1;

use App\OutgoingLetter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use JWTAuth;

class OutgoingLetterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = [];

        if (!JWTAuth::user()->id) {
            return response()->format(404, 'You cannot access this page', null);
        } else {
            $limit = $request->filled('limit') ? $request->input('limit') : 10;
            $sort = $request->filled('sort') ? ['letter_date ' . $request->input('sort') ] : ['letter_date ASC'];

            try {
                $data = OutgoingLetter::where('user_id',  JWTAuth::user()->id)
                ->where(function ($query) use ($request) {
                    if ($request->filled('letter_number')) {
                        $query->where('letter_number', 'LIKE', "%{$request->input('letter_number')}%");
                    }

                    if ($request->filled('letter_date')) {
                        $query->where('letter_date', $request->input('letter_date'));
                    }
                })         
                ->orderByRaw(implode($sort))
                ->paginate($limit);
            } catch (\Exception $exception) {
                return response()->format(400, $exception->getMessage());
            }
        }

        return response()->format(200, 'success', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    { 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\OutgoingLetter  $outgoingLetter
     * @return \Illuminate\Http\Response
     */
    public function show(OutgoingLetter $outgoingLetter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\OutgoingLetter  $outgoingLetter
     * @return \Illuminate\Http\Response
     */
    public function edit(OutgoingLetter $outgoingLetter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\OutgoingLetter  $outgoingLetter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OutgoingLetter $outgoingLetter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\OutgoingLetter  $outgoingLetter
     * @return \Illuminate\Http\Response
     */
    public function destroy(OutgoingLetter $outgoingLetter)
    {
        //
    }
}
