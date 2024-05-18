<?php

namespace Database\Factories;

use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FriendRequest>
 */
class FriendRequestFactory extends Factory
{

    public function definition(): array
    {
        return [
            'user_id' => User::all()->random()->first(),
            'receiver_id' => User::all()->random()->first(),
            'status' => $this->faker->randomElement([FriendRequest::STATUS_REJECTED,
                FriendRequest::STATUS_PENDING, FriendRequest::STATUS_ACCEPTED]),
        ];
    }
}
