<?php

namespace Tests\Feature\TaskController;

use App\Models\Task;
use App\Models\TaskUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class StoreTaskTest extends TestCase
{
    use RefreshDatabase;
    use TaskControllerHelpersTrait;


    public function test_creating_a_new_task()
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'This is a new task',
        ];

        $responseData = $this->createNewTask($taskData, Response::HTTP_CREATED);

        $id = $responseData['data']['task']['id'];
        $userId = $responseData['data']['task']['user_id'];
        $endDate = $responseData['data']['task']['end_date'];
        $description = $responseData['data']['task']['description'];

        $task = Task::find($id);
        $this->assertEquals($id, $task->id);
        $this->assertEquals($userId, $task->user_id);
        $this->assertEquals($endDate, $task->end_date);
        $this->assertEquals($description, $task->description);

        $taskUser = TaskUser::where('task_id', $id)->first();
        $this->assertEquals($taskUser->task_id, $task->id);
    }

    static function invalid_task_data_provider()
    {
        return [
            [['description' => 'Test Description']],
            [['title' => '', 'description' => 'Test Description']],
            [['title' => 'Test Name', 'description' => 'description', 'end_date' => 'zazaz' ]]
        ];
    }

    /**
     * @dataProvider invalid_task_data_provider
     */
    public function test_store_invalid_data($taskData)
    {
        $this->createNewTask($taskData, Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertCount(0, Task::all());
    }
}