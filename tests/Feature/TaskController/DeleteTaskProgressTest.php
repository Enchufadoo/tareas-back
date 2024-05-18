<?php

namespace Tests\Feature\TaskController;

use App\Models\Task;
use App\Models\TaskProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DeleteTaskProgressTest extends TestCase
{
    use RefreshDatabase;
    use TaskControllerHelpersTrait;


    public function test_deleting_progress_from_a_task()
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'This is a new task',
        ];

        $this->createNewTask($taskData, Response::HTTP_CREATED);
        $task = Task::first();

        $this->createNewProgress($task->id, Response::HTTP_CREATED);

        $this->assertCount(1, TaskProgress::all());

        $this->deleteProgress($task->id, Response::HTTP_OK);
    }

    public function test_deleting_progress_from_a_task_when_there_is_no_progress()
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'This is a new task',
        ];

        $this->createNewTask($taskData, Response::HTTP_CREATED);
        $task = Task::first();

        $this->assertCount(0, TaskProgress::all());

        $this->deleteProgress($task->id, Response::HTTP_OK);

        $this->assertCount(0, TaskProgress::all());
    }

    public function test_deleting_progress_from_a_task_that_does_not_exist()
    {
        $taskId = 777;

        $task = Task::find($taskId);
        $this->assertNull($task);

        $this->deleteProgress($taskId, Response::HTTP_NOT_FOUND);
    }

    public function test_deleting_progress_from_another_user_task_fails()
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'This is a new task',
        ];

        $this->createNewTask($taskData, Response::HTTP_CREATED);
        $task = Task::first();

        $this->createNewProgress($task->id, Response::HTTP_CREATED);

        $this->assertCount(1, TaskProgress::all());

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->deleteProgress($task->id, Response::HTTP_NOT_FOUND);

    }
}