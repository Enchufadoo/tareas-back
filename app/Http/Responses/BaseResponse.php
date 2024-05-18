<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class BaseResponse
{

    /**
     * Returns a JSON response with the given data, message, status, and optional errors.
     *
     * @param array $data
     * @param string $message
     * @param int $status
     * @param array $errors
     * @return JsonResponse The JSON response object
     */
    public function json(array $data, string $message, int $status, array $errors = [])
    {
        $response = [
            'data' => $data,
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}
