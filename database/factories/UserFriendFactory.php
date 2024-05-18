<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFriendFactory extends Factory
{
    public function definition()
    {

        return [
            'user_id' => User::all()->random()->first(),
            'friend_id' => User::all()->random()->first(),
        ];
    }
}
