<?php

namespace Feature;

use App\Models\FriendRequest;
use App\Models\User;
use App\Tests\Utils;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class FriendControllerTest extends TestCase
{
    use RefreshDatabase;
    use Utils;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->user = User::first();

        $this->actingAs($this->user);
    }

    public function test_looking_for_a_username_of_a_friend_succeeds()
    {
        $username = 'zazaza123';
        User::factory()->create(['username' => $username]);

        $response = $this->json('GET', '/api/friend/username', ['username' => $username]);

        $response->assertStatus(Response::HTTP_OK);

        $responseData = $this->convertToJson($response);
        $this->assertNull($responseData['data']['sent_request']);
        $this->assertNull($responseData['data']['received_request']);

        $this->assertTrue($responseData['data']['exists']);
    }

    public function test_looking_for_a_username_of_a_friend_succeeds_and_returns_previous_requests()
    {
        $username = 'zazaza123';
        User::factory()->create(['username' => $username]);

        $response = $this->json('POST', '/api/friend/request', ['username' => $username]);
        $response->assertStatus(Response::HTTP_CREATED);

        $response = $this->json('GET', '/api/friend/username', ['username' => $username]);

        $response->assertStatus(Response::HTTP_OK);

        $responseData = $this->convertToJson($response);

        $this->assertNotNull($responseData['data']['sent_request']);
        $this->assertNull($responseData['data']['received_request']);

        $this->assertEquals(FriendRequest::STATUS_PENDING, $responseData['data']['sent_request']['status']);
        $this->assertNotNull($responseData['data']['sent_request']['created_at']);

        $this->assertTrue($responseData['data']['exists']);
    }

    public function test_looking_for_a_username_of_a_friend_succeeds_and_returns_previous_requests_two()
    {
        $username = 'zazaza123';
        $otherUser = User::factory()->create(['username' => $username]);

        FriendRequest::factory()->create(['user_id' => $otherUser->id, 'receiver_id' => $this->user->id,
            'status' => FriendRequest::STATUS_PENDING]);

        $response = $this->json('GET', '/api/friend/username', ['username' => $username]);

        $response->assertStatus(Response::HTTP_OK);

        $responseData = $this->convertToJson($response);

        $this->assertNull($responseData['data']['sent_request']);
        $this->assertNotNull($responseData['data']['received_request']);

        $this->assertEquals(FriendRequest::STATUS_PENDING, $responseData['data']['received_request']['status']);
        $this->assertNotNull($responseData['data']['received_request']['created_at']);

        $this->assertTrue($responseData['data']['exists']);
    }

    public function test_looking_for_a_username_of_a_non_existent_friend_succeeds()
    {
        $username = 'zazaza123';
        User::factory()->create(['username' => $username]);

        $response = $this->json('GET', '/api/friend/username', ['username' => 'wrongusername']);
        $response->assertStatus(Response::HTTP_OK);

        $responseData = $this->convertToJson($response);
        $this->assertNull($responseData['data']['sent_request']);
        $this->assertNull($responseData['data']['received_request']);

        $this->assertFalse($responseData['data']['exists']);
    }

    public function test_sending_friend_request_works()
    {
        $username = 'zazaza123';
        $friend = User::factory()->create(['username' => $username]);

        $response = $this->json('POST', '/api/friend/request', ['username' => $username]);

        $response->assertStatus(Response::HTTP_CREATED);

        $friendRequest = FriendRequest::first();

        $this->assertModelExists($friendRequest);
        $this->assertEquals($friend->id, $friendRequest->receiver_id);
        $this->assertEquals($this->user->id, $friendRequest->user_id);
        $this->assertEquals('pending', $friendRequest->status);
    }

    public function test_sending_friend_request_when_theres_a_previous_request_from_user_fails()
    {
        $username = 'zazaza123';
        User::factory()->create(['username' => $username]);

        $response = $this->json('POST', '/api/friend/request', ['username' => $username]);
        $response->assertStatus(Response::HTTP_CREATED);

        $response = $this->json('POST', '/api/friend/request', ['username' => $username]);
        $response->assertStatus(Response::HTTP_CONFLICT);
    }

    public function test_sending_friend_request_when_theres_a_previous_request_from_friend_fails()
    {
        $username = 'zazaza123';
        $otherUser = User::factory()->create(['username' => $username]);

        FriendRequest::factory()->create(['user_id' => $otherUser->id, 'receiver_id' => $this->user->id]);

        $response = $this->json('POST', '/api/friend/request', ['username' => $username]);
        $response->assertStatus(Response::HTTP_CONFLICT);
    }

    public function test_listing_friend_requests_for_user_succeeds()
    {
        $response = $this->json('GET', '/api/friend/request');
        $responseData = $this->convertToJson($response);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount(0, $responseData['data']['requests']);
    }

    public function test_listing_friend_requests_for_user_with_multiple_friends_succeeds()
    {
        $firstFriendUsername = 'zazaza123';
        $firstFriend = User::factory()->create(['username' => $firstFriendUsername]);

        $secondFriendUsername = 'pepe123456';
        $secondFriend = User::factory()->create(['username' => $secondFriendUsername]);

        $response = $this->json('POST', '/api/friend/request', ['username' => $firstFriendUsername]);
        $response->assertStatus(Response::HTTP_CREATED);

        $response = $this->json('POST', '/api/friend/request', ['username' => $secondFriendUsername]);
        $response->assertStatus(Response::HTTP_CREATED);

        $response = $this->json('GET', '/api/friend/request');
        $responseData = $this->convertToJson($response);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount(2, $responseData['data']['requests']);
        $this->assertEquals($this->user->username, $responseData['data']['requests'][0]['from']);
        $this->assertEquals($firstFriend->username, $responseData['data']['requests'][0]['to']);
        $this->assertEquals(FriendRequest::STATUS_PENDING, $responseData['data']['requests'][0]['status']);
        $this->assertEquals($this->user->username, $responseData['data']['requests'][1]['from']);
        $this->assertEquals($secondFriend->username, $responseData['data']['requests'][1]['to']);
        $this->assertEquals(FriendRequest::STATUS_PENDING, $responseData['data']['requests'][1]['status']);
    }

}
