<?php

namespace App\Http\Responses;

use Illuminate\Http\Response;

class LoginResponse
{
    public const METHOD_GOOGLE = 'GOOGLE';
    public const METHOD_EMAIL = 'EMAIL';

    public function success(string $token, string $method, int $responseStatus = Response::HTTP_OK)
    {
        $baseResponse = new BaseResponse();
        return $baseResponse->json([
            'token' => $token
        ], 'Login successful wtih ' . $method, $responseStatus);
    }

}