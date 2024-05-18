<?php

namespace Database\Factories;

use App\Models\Setting;
use App\Models\SettingValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SettingSettingValue>
 */
class SettingSettingValueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'setting_id' => Setting::factory(),
            'value_id' => SettingValue::factory()
        ];
    }
}
