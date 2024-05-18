<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class SetUsernameRequest extends FormRequest
{
    public function rules()
    {
        return [
            'username' => [...User::USER_USERNAME_VALIDATION_RULES, 'required'],
        ];
    }
}
