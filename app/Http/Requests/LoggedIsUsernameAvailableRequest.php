<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoggedIsUsernameAvailableRequest extends FormRequest
{

    public function rules()
    {
        return [
            'username' => [
                'required',
                'string'

            ],
        ];
    }
}
