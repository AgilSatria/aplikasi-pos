<?php

namespace App\Helpers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class ApiResponse
{
    public static function success(
        JsonResource|array|null $data = null,
        string $message = 'Success',
        int $status = Response::HTTP_OK

    ) {

        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }


    public static function error(

        string $message,
        int $status = Response::HTTP_BAD_REQUEST,
        array $errors = []

    ) {

        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }
}
