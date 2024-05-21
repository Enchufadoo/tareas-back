<?php

namespace App\Http\Requests;

use App\Models\FriendRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResolveFriendRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([FriendRequest::STATUS_ACCEPTED, FriendRequest::STATUS_REJECTED]),
            ],
            
        ];
    }
}
