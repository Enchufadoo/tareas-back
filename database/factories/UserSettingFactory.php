<?php

namespace Database\Factories;

use App\Models\Setting;
use App\Models\SettingValue;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserSetting>
 */
class UserSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'setting_id' => Setting::factory(),
            'value_id' => SettingValue::factory()
        ];
    }
}
