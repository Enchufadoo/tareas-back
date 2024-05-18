<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListTasksRequest extends FormRequest
{
    public function rules()
    {
        return [
            'finished' => 'required|integer'
        ];
    }
}
