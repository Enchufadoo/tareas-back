<?php

namespace Tests\Feature\TaskController;


use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ShowTaskListTest extends TestCase
{
    use RefreshDatabase;
    use TaskControllerHelpersTrait;

    public function test_showing_list_the_tasks_with_no_tasks()
    {
        $responseData = $this->showTaskList(Response::HTTP_OK, ['finished' => Task::TASK_UNFINISHED]);

        $this->assertEquals([], $responseData['data']['tasks']);

        $this->assertEquals(0, $responseData['data']['task_count']['finished']);
        $this->assertEquals(0, $responseData['data']['task_count']['unfinished']);
    }

    public function test_showing_list_the_tasks_with_unfinished_task()
    {
        $task = Task::factory()->create(['finished' => Task::TASK_UNFINISHED]);

        $responseData = $this->showTaskList(Response::HTTP_OK, ['finished' => Task::TASK_UNFINISHED]);

        $this->assertEquals($task->id, $responseData['data']['tasks'][0]['id']);

        $this->assertEquals(0, $responseData['data']['task_count']['finished']);
        $this->assertEquals(1, $responseData['data']['task_count']['unfinished']);
    }
    public function test_showing_list_the_tasks_with_finished_tasks()
    {
        Task::factory()->create(['finished' => Task::TASK_FINISHED]);
        Task::factory()->create(['finished' => Task::TASK_FINISHED]);
        Task::factory()->create(['finished' => Task::TASK_UNFINISHED]);

        $responseData = $this->showTaskList(Response::HTTP_OK, ['finished' => Task::TASK_UNFINISHED]);

        $this->assertEquals(2, $responseData['data']['task_count']['finished']);
        $this->assertEquals(1, $responseData['data']['task_count']['unfinished']);



    }

    public function test_showing_list_the_tasks_with_finished_task_and_progress_today()
    {
        $task = Task::factory()->create(['finished' => Task::TASK_UNFINISHED]);

        $this->createNewProgress($task->id, Response::HTTP_CREATED);

        $responseData = $this->showTaskList(Response::HTTP_OK, ['finished' => Task::TASK_UNFINISHED]);

        $this->assertEquals($task->id, $responseData['data']['tasks'][0]['id']);
        $this->assertEquals(true, $responseData['data']['tasks'][0]['progress_today']);
    }

    public function test_showing_list_finished_tasks()
    {
        $task = Task::factory()->create(['finished' => Task::TASK_FINISHED]);

        $responseData = $this->showTaskList(Response::HTTP_OK, ['finished' => Task::TASK_FINISHED]);

        $this->assertCount(1, $responseData['data']['tasks']);
        $this->assertEquals($task->id, $responseData['data']['tasks'][0]['id']);
    }


}