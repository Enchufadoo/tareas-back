<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoggedIsEmailAvailableRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'email',
                'required'
            ],
        ];
    }
}
