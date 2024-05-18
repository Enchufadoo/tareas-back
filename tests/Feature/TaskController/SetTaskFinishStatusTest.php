<?php

namespace Tests\Feature\TaskController;

use App\Models\Task;
use App\Models\TaskUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SetTaskFinishStatusTest extends TestCase
{
    use RefreshDatabase;
    use TaskControllerHelpersTrait;


    public function test_marking_task_as_finished()
    {
        $task = Task::factory()->create(['finished' => Task::TASK_UNFINISHED]);

        $this->setTaskFinishedStatus(Response::HTTP_OK, $task->id, Task::TASK_FINISHED);

        $finishedTask = Task::find($task->id);
        $this->assertEquals(Task::TASK_FINISHED, $finishedTask->finished);
    }

    public function test_marking_task_as_unfinished()
    {
        $task = Task::factory()->create(['finished' => Task::TASK_FINISHED]);

        $this->setTaskFinishedStatus(Response::HTTP_OK, $task->id, Task::TASK_UNFINISHED);

        $finishedTask = Task::find($task->id);
        $this->assertEquals(Task::TASK_UNFINISHED, $finishedTask->finished);
    }

    public function test_marking_another_users_task_as_finished_fails()
    {
        $task = Task::factory()->create(['finished' => Task::TASK_UNFINISHED]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->setTaskFinishedStatus(Response::HTTP_NOT_FOUND, $task->id, Task::TASK_UNFINISHED);

        $finishedTask = Task::find($task->id);
        $this->assertEquals(Task::TASK_UNFINISHED, $finishedTask->finished);
    }

    public function test_marking_task_as_finished_with_invalid_task_id_fails()
    {
        $randomId = 999;
        $task = Task::find($randomId);
        $this->assertNull($task);

        $this->setTaskFinishedStatus(Response::HTTP_NOT_FOUND, $randomId, Task::TASK_FINISHED);
    }
}