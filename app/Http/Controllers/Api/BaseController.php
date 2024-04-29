<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class BaseController extends Controller
{
    public function success($data, $message = 'Success', $color = "4BB543")
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'message_color' => $color,
        ], 200);
    }

    public function error($message, $exception = false, $color = "C91432")
    {
        Log::channel('api')->error($message);
        $message = ($exception) ? (config('app.debug') ? $message : __('Internet Server Error! Please contact customer support')) : $message;
        return response()->json([
            'success' => false,
            'message' => $message,
            'message_color' => $color,
        ], 400);
    }

    public function errorWithData($message, $data, $color = "C91432")
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
            'message_color' => $color,
        ], 400);
    }

    public function errorWithCode($message, $code, $color = "C91432")
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code' => $code,
            'message_color' => $color,
        ], 400);
    }

    public function errorWithCodeAndData($message, $code, $data, $color = "C91432")
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code' => $code,
            'data' => $data,
            'message_color' => $color,
        ], 400);
    }

}
