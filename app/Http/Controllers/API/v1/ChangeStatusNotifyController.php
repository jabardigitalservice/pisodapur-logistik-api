<?php

namespace App\Http\Controllers\API\v1;

use App\User;
use App\Http\Controllers\Controller;
use App\Notifications\TestResult;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ChangeStatusNotifyController extends Controller
{
    public function sendNotification(Request $request)
    {
        $role = $request->input('roles', 'superadmin');
        $users = User::where('roles', $role)->get();
        foreach ($users as $user) {
            $user->notify(new TestResult());
        }

        return response()->json(['message' => 'OK']);
    }
}