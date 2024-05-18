<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IsEmailAvailableRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'email|required',
        ];
    }
}
