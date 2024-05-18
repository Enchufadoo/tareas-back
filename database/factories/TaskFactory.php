<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition()
    {
        return [
            'title' => fake()->text(10),
            'finished' => fake()->boolean(),
            'description' => mt_rand(0, 1) ? fake()->text(30) : null,
            'user_id' => auth()->user()->id,
        ];
    }
}
