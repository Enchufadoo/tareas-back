<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => [...User::USER_NAME_VALIDATION_RULES, 'sometimes'],
            'username' => [...User::USER_USERNAME_VALIDATION_RULES, 'sometimes'],
            'avatar' => 'sometimes|mimes:jpg,jpeg,png|max:2048'
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach (array_keys($validator->getRules()) as $field) {
                if (isset($validator->getData()[$field])) {
                    return;
                }
            }

            $validator->errors()->add('all', 'At least one field is required.');
        });
    }

}