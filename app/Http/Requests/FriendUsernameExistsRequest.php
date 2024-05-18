<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class FriendUsernameExistsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => [...User::USER_USERNAME_VALIDATION_RULES, 'required']
        ];
    }
}
