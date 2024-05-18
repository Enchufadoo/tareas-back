<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IsUsernameAvailableRequest extends FormRequest
{

    public function rules()
    {
        return [
            'username' => [
                'required',
                'alpha_num',
                'min:8',
                'max:20',
            ],
        ];
    }
}
