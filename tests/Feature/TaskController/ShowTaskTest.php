<?php

namespace Tests\Feature\TaskController;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ShowTaskTest extends TestCase
{
    use RefreshDatabase;
    use TaskControllerHelpersTrait;

    public function test_showing_task_works()
    {
        $task = Task::factory()->create();

        $responseData = $this->showTask($task->id, Response::HTTP_OK);

        $this->assertEquals($task->id, $responseData['data']['task']['id']);
        $this->assertEquals($task->user_id, $responseData['data']['task']['user_id']);
        $this->assertEquals($task->end_date, $responseData['data']['task']['end_date']);
        $this->assertEquals($task->description, $responseData['data']['task']['description']);
        $this->assertEquals(0, $responseData['data']['task']['progress_today']);
    }

    public function test_showing_task_with_progress_works()
    {
        $task = Task::factory()->create();

        $this->createNewProgress($task->id, Response::HTTP_CREATED);

        $responseData = $this->showTask($task->id, Response::HTTP_OK);

        $this->assertEquals($task->id, $responseData['data']['task']['id']);
        $this->assertEquals($task->user_id, $responseData['data']['task']['user_id']);
        $this->assertEquals($task->end_date, $responseData['data']['task']['end_date']);
        $this->assertEquals($task->description, $responseData['data']['task']['description']);

        $this->assertEquals(1, $responseData['data']['task']['progress_today']);
    }

    public function test_showing_task_from_another_user_fails()
    {
        $task = Task::factory()->create();
        $this->showTask($task->id, Response::HTTP_OK);

        $this->actingAs(User::factory()->create());
        $this->showTask($task->id, Response::HTTP_NOT_FOUND);
    }

    public function test_showing_a_task_that_doesnt_exist_fails()
    {
        $randomId = 999;
        $task = Task::find($randomId);
        $this->assertNull($task);

        $this->showTask($randomId, Response::HTTP_NOT_FOUND);
    }
}