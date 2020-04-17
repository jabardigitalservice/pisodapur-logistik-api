<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Agency;
use App\Applicant;
use JWTAuth;

class LogisticSubmissionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (JWTAuth::user()->roles != 'dinkesprov') {
                return response()->format(404, 'You cannot access this page', null);
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $limit = $request->filled('limit') ? $request->input('limit') : 10;
        $data = Agency::with('applicant')
            ->paginate($limit);

        return response()->format(200, 'success', $data);
    }

    public function show($id)
    {
        // To do: get logistic submission detail
    }
}
