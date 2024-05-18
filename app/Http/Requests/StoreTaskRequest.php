<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|max:30|min:4',
            'description' => 'nullable|min:4|max:3000',
            'end_date' => 'nullable|date',
        ];
    }
}
