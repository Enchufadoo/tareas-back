<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SettingValue>
 */
class SettingValueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'value' => fake()->text(10)
        ];
    }
}
