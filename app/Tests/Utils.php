<?php

namespace App\Tests;

use Illuminate\Testing\TestResponse;

trait Utils{

    /**
     * Decode the JSON content of a TestResponse object.
     *
     * @param TestResponse $response The TestResponse object to decode.
     * @return mixed The decoded JSON content.
     */
    protected function convertToJson(TestResponse $response){
        return json_decode($response->getContent(), true);
    }
}