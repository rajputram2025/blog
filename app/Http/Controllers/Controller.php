<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    //

     use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

     public function sendResponse($result, $message,$requestkey)
    {
        $response = [
            'status' => 'SUCCESS',
            'response'    => $result,
            'message' => $message,
            'requestKey'=>$requestkey,
        ];


        return response()->json($response, 200,[],JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function sendError($requestkey,$errorMessages)
    {
    	$response = [
            'status' => 'FAILURE',
            'message' => $errorMessages,
            'requestKey'=>$requestkey,
        ];


        return response()->json($response, 200);
    }

    public static function quickRandom($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }
}
