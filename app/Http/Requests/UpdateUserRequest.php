<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function rules()
    {
        $rules = [
            'name' => [...User::USER_NAME_VALIDATION_RULES, 'sometimes'],
            'username' => [...User::USER_USERNAME_VALIDATION_RULES, 'sometimes'],
            'avatar' => 'sometimes|mimes:jpg,jpeg,png|max:2048'
        ];

        return $rules;
    }
}