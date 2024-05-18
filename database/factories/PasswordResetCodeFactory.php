<?php

namespace Database\Factories;

use App\Managers\PasswordReset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PasswordResetCode>
 */
class PasswordResetCodeFactory extends Factory
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
            'attempts' => 0,
            'expiry_date' => fake()->dateTimeBetween('now', '+10 minutes'),
            'code' => (new PasswordReset())->createCode()
        ];
    }
}
