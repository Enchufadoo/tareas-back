<?php

namespace Tests\Feature\TaskController;

use App\Models\User;
use App\Tests\Utils;
use Database\Seeders\UserSeeder;

trait TaskControllerHelpersTrait
{
    use Utils;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->user = User::first();

        $this->actingAs($this->user);
    }

    public function createNewTask(array $taskData, int $expectedStatus)
    {
        $response = $this->json('POST', '/api/task', $taskData);
        $response->assertStatus($expectedStatus);
        return $this->convertToJson($response);
    }

    public function createNewProgress(int $taskId, int $expectedStatus)
    {
        $response = $this->json('POST', '/api/task/progress/' . $taskId);
        $response->assertStatus($expectedStatus);
    }

    public function deleteProgress(int $taskId, int $expectedStatus)
    {
        $response = $this->json('DELETE', '/api/task/progress/' . $taskId);
        $response->assertStatus($expectedStatus);
    }

    public function getStoreTaskData(int $expectedStatus)
    {
        $response = $this->json('GET', '/api/task/store/data');
        $response->assertStatus($expectedStatus);
        return $this->convertToJson($response);
    }

    public function showTask(int $taskId, int $expectedStatus)
    {
        $response = $this->json('GET', '/api/task/' . $taskId);
        $response->assertStatus($expectedStatus);
        return $this->convertToJson($response);
    }

    public function showTaskList(int $expectedStatus, array $data)
    {
        $response = $this->json('GET', '/api/task', $data);
        $response->assertStatus($expectedStatus);
        return $this->convertToJson($response);
    }

    public function setTaskFinishedStatus(int $expectedStatus, int $taskId, int $status)
    {
        $response = $this->json('PUT', '/api/task/finish/' . $taskId . '/' . $status);
        $response->assertStatus($expectedStatus);
        return $this->convertToJson($response);
    }

    public function deleteTask(int $expectedStatus, int $taskId)
    {
        $response = $this->json('DELETE', '/api/task/' . $taskId);
        $response->assertStatus($expectedStatus);
        return $this->convertToJson($response);
    }
}
