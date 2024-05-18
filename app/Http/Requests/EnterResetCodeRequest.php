<?php

namespace App\Http\Requests;

use App\Managers\PasswordReset;
use Illuminate\Foundation\Http\FormRequest;

class EnterResetCodeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'code' => ['required', 'size:' . PasswordReset::RECOVERY_CODE_LENGTH]
        ];
    }
}
