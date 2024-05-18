<?php

namespace Tests\Feature\TaskController;

use App\Models\Task;
use App\Models\TaskUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DeleteTaskTest extends TestCase
{
    use RefreshDatabase;
    use TaskControllerHelpersTrait;

    public function test_deleting_task()
    {
        $task = Task::factory()->create();

        $this->deleteTask(Response::HTTP_OK, $task->id);

        $task->refresh();

        $this->assertNotNull($task->deleted_at);
        $this->assertTrue($task->trashed());
    }

    public function test_deleting_non_existent_task_fails()
    {
        $task = Task::factory()->create(['id' => 33]);

        $this->deleteTask(Response::HTTP_NOT_FOUND, 99);

        $task->refresh();

        $this->assertNull($task->deleted_at);
        $this->assertFalse($task->trashed());
    }

    public function test_deleting_anothers_user_task_fails()
    {
        $user = User::factory()->create();

        $task = Task::factory()->create([ 'user_id' => $user->id]);

        $this->deleteTask(Response::HTTP_NOT_FOUND, $task->id);

        $task->refresh();

        $this->assertNull($task->deleted_at);
        $this->assertFalse($task->trashed());
    }

}