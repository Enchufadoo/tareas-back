<?php

namespace Feature\TaskController;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\TaskController\TaskControllerHelpersTrait;
use Tests\TestCase;

class WeeksTaskProgressTest extends TestCase
{
    use RefreshDatabase;
    use TaskControllerHelpersTrait;

    public function test_listing_test_progress_works()
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'This is a new task',
        ];

        $response = $this->createNewTask($taskData, Response::HTTP_CREATED);
        $firstTaskId = $response['data']['task']['id'];
        $this->createNewProgress($firstTaskId, Response::HTTP_CREATED);

        $response = $this->createNewTask($taskData, Response::HTTP_CREATED);
        $secondTaskId = $response['data']['task']['id'];
        $this->createNewProgress($secondTaskId, Response::HTTP_CREATED);
        $this->setTaskFinishedStatus(Response::HTTP_OK, $secondTaskId, Task::TASK_FINISHED);

        $response = $this->createNewTask($taskData, Response::HTTP_CREATED);
        $thirdTaskId = $response['data']['task']['id'];
        $this->setTaskFinishedStatus(Response::HTTP_OK, $thirdTaskId, Task::TASK_FINISHED);

        $weeksProgress = $this->showWeeksProgress(Response::HTTP_OK, []);

        $progress = $weeksProgress['data']['progress'];
        $tasks = $weeksProgress['data']['tasks'];

        $this->assertCount(2, $progress);
        $this->assertCount(2, $tasks);

        $this->assertCount(7, $progress[$firstTaskId]);
        $this->assertCount(7, $progress[$secondTaskId]);
    }
}