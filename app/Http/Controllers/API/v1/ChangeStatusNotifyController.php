<?php

namespace App\Http\Controllers\API\v1;

use App\User;
use App\Validation;
use App\Http\Controllers\Controller;
use App\Notifications\ChangeStatusNotification;
use Illuminate\Http\Request;

class ChangeStatusNotifyController extends Controller
{
    public function sendNotification(Request $request)
    {
        $param = [ 
            'id' => 'required',
            'url' => 'required',
            'phase' => 'required',
        ];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() === 200) {
            $notify = [];
            $users = User::where('phase', $request->phase)->where('handphone', '!=', '')->get();
            $requiredData = [
                'id' => $request->id,
                'url' => $request->url,
                'phase' => $request->phase,
            ];
            foreach ($users as $user) {
                $notify[] = $user->notify(new ChangeStatusNotification($requiredData));
            }
            $responseData = [
                'request' => $request->all(),
                'users' => $users,
                'notify' => $notify,
            ];
            $response = response()->format(200, 'success', $responseData);
        }
        return $response;
    }
}