<?php

namespace App\Http\Requests;

use App\Managers\PasswordReset;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordForResetRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'renewal_token' => ['required', 'size:' . PasswordReset::RENEWAL_TOKEN_LENGTH],
            'password' => 'required|min:5|max:20',
            'code' => ['required', 'size:' . PasswordReset::RECOVERY_CODE_LENGTH]
        ];
    }
}
