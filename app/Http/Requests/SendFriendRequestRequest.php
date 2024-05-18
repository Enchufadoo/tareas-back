<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class SendFriendRequestRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'username' => ['required',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $receiver = User::findUsernameAnotherUser($value);

                    if (!$receiver) {
                        $fail('Username not found');
                    }
                }],
        ];
    }
}
