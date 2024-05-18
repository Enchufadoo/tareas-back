<?php

namespace App\Http\Controllers;

use App\Http\Responses\BaseResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function json(
        array  $data,
        string $message = '',
        ?int   $status = Response::HTTP_OK,
        ?array $errors = []
    ) {
        $baseResponse = new BaseResponse();
        return $baseResponse->json($data, $message, $status, $errors);
    }
}
