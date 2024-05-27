<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmailRegistrationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => 'required|min:5|max:20|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:7|max:20',
            'name' => 'required|min:5|max:50',
        ];
    }
}
