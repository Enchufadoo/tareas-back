<?php

namespace App\Http\Requests;

use App\Models\SettingValue;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'value' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $settingValue = SettingValue::where(
                        [
                            ['value', '=', $value],
                            ['setting_id', '=', $this->setting->id]
                        ]
                    )->first();

                    if (!$settingValue) {
                        $fail('Value not available for this setting');
                    }
                }
            ]
        ];
    }
}
