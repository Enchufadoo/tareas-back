<?php

namespace Tests\Feature\TaskController;





use App\Models\Task;
use App\Models\TaskProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AddTaskProgressTest extends TestCase
{
    use RefreshDatabase;
    use TaskControllerHelpersTrait;


    public function test_adding_progress_to_a_task()
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'This is a new task',
        ];

        $this->createNewTask($taskData, Response::HTTP_CREATED);
        $task = Task::first();

        $this->createNewProgress($task->id, Response::HTTP_CREATED);

        $progress = TaskProgress::where('task_id', $task->id)->first();
        $this->assertEquals($progress->user_id, $this->user->id);
    }

    public function test_adding_progress_to_a_task_multiple_times_in_a_day_only_adds_once()
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'This is a new task',
        ];

        $this->createNewTask($taskData, Response::HTTP_CREATED);
        $task = Task::first();

        $this->assertCount(0, TaskProgress::all());

        $this->createNewProgress($task->id, Response::HTTP_CREATED);

        $progress = TaskProgress::where('task_id', $task->id)->first();
        $this->assertEquals($progress->user_id, $this->user->id);

        $this->assertCount(1, TaskProgress::all());

        $this->createNewProgress($task->id, Response::HTTP_OK);

        $this->assertCount(1, TaskProgress::all());

    }

    public function test_adding_progress_to_a_task_from_the_different_user_fails()
    {
        $anotherUser = User::factory()->create();

        $taskData = [
            'title' => 'New Task',
            'description' => 'This is a new task',
        ];

        $this->createNewTask($taskData, Response::HTTP_CREATED);
        $task = Task::first();

        $this->actingAs($anotherUser);
        $this->createNewProgress($task->id, Response::HTTP_NOT_FOUND);

        $this->assertCount(0, TaskProgress::all());
    }
}