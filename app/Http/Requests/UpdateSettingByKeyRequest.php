<?php

namespace App\Http\Requests;

use App\Models\SettingSettingValue;
use App\Models\SettingValue;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingByKeyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'value_key' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $settingValue = SettingValue::where('value', $value);
            
                    $value = SettingSettingValue::where(
                        [
                            ['value_id', '=', $value],
                            ['setting_id', '=', $this->setting->id]
                        ]
                    )->first();

                    if (!$value) {
                        $fail('Value not found');
                    }
                }
            ]
        ];
    }
}
