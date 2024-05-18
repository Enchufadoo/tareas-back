<?php

namespace Tests\Feature\TaskController;

use App\Models\User;
use App\Models\UserFriend;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class StoreTaskDataTest extends TestCase
{
    use RefreshDatabase;
    use TaskControllerHelpersTrait;

    public function test_obtaining_data_for_creating_a_task_with_no_friends()
    {
        $responseData = $this->getStoreTaskData(Response::HTTP_OK);
        $this->assertEquals($responseData['data']['friends'], []);
    }

    public function test_obtaining_data_for_creating_a_task_with_friend()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        UserFriend::factory()->create(['user_id' => $user1->id, 'friend_id' => $this->user->id]);
        UserFriend::factory()->create(['user_id' => $this->user->id, 'friend_id' => $user1->id]);

        $responseData = $this->getStoreTaskData(Response::HTTP_OK);

        $firstFriend = $responseData['data']['friends'][0];

        $this->assertEquals($firstFriend['user_id'], $user1->id);

    }

}