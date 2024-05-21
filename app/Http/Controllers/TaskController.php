<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Models\Task;
use App\Models\TaskProgress;
use App\Models\TaskUser;
use App\Models\UserFriend;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskController extends Controller
{

    /**
     * Add progress to a task for the current user.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function addProgress(Task $task): JsonResponse
    {
        $progressToday = TaskProgress::currentUser()->fromTask($task->id)
            ->today()->first();

        if (!$progressToday) {
            $newProgress = new TaskProgress(['user_id' => $task->user_id, 'task_id' => $task->id]);
            $newProgress->save();
            return $this->json([], 'Progress added', Response::HTTP_CREATED);
        }

        return $this->json([], 'Progress already today, skipping');
    }

    /**
     * Remove progress from a task for the current user.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function removeProgress(Task $task)
    {
        $progressToday = TaskProgress::where(['user_id' => $task->user_id, 'task_id' => $task->id])
            ->whereRaw('Date(created_at) = CURDATE()')->first();

        if ($progressToday) {
            $progressToday->delete();
            return $this->json([], 'Progress removed');
        }

        return $this->json([], 'No progress today, skipping');
    }

    /**
     * Store a new task.
     *
     * @param StoreTaskRequest $request
     * @return JsonResponse
     */
    public function store(StoreTaskRequest $request)
    {
        $title = $request->input('title');
        $description = $request->input('description');
        $endDate = $request->input('end_date');

        $task = new Task();

        $task->title = $title;
        $task->description = $description;
        $task->end_date = $endDate;

        $task->user_id = auth()->user()->id;

        $task->save();

        $taskUsers = new TaskUser();

        $taskUsers->user_id = auth()->user()->id;
        $taskUsers->task_id = $task->id;
        $taskUsers->finished = 0;

        $taskUsers->save();

        return $this->json(['task' => $task], 'created', Response::HTTP_CREATED);
    }

    /**
     * Store task data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function dataStoreTask(Request $request)
    {
        $friends = UserFriend::where('user_id', auth()->user()->id)->orWhere('friend_id', auth()->user()->id)->get();
        return $this->json(['friends' => $friends], 'Data to create a task');
    }

    /**
     * Display the specified task.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function show(Task $task)
    {
        //sleep(4);
        //return $this->json([], 'Task data', Response::HTTP_INTERNAL_SERVER_ERROR);
        $taskArray = $task->toArray();
        $userId = auth()->user()->id;

        $taskProgress = TaskProgress::currentUser($userId)->fromTask($task->id)->today()->first();

        $taskArray['progress_today'] = $taskProgress ? 1 : 0;

        $data = [
            'task' => $taskArray
        ];

        return $this->json($data, 'Task data');
    }

    /**
     * Get the list of tasks.
     *
     * @param ListTasksRequest $request
     * @return JsonResponse
     */
    public function listTasks(ListTasksRequest $request)
    {
        $noFilter = "2";
        $userId = auth()->user()->id;

        $filter = [
            'tasks.user_id' => $userId,
        ];

        if ($request->get('finished') !== $noFilter) {
            $filter['finished'] = $request->get('finished');
        }

        $tasks = Task::select()->addSelect(
            ['hours_since_progress' => TaskProgress::selectRaw('HOUR(TIMEDIFF(now(), created_at))')
                ->whereRaw('task_id = tasks.id')->where(['user_id' => $userId])
                ->orderByDesc('created_at')->limit(1)]
        )->where($filter)->get();

        foreach ($tasks as $key => $task) {
            $tasks[$key]['last_progress'] = Task::find($task['id'])->taskProgress()
                ->where(['user_id' => $userId])->orderByDesc('created_at')->first();

            $tasks[$key]['progress_today'] = $task['hours_since_progress'] < 24 && !is_null($task['hours_since_progress']);
        }

        $finishedTasks = Task::currentUser()->finished()->count();
        $unfinishedTasks = Task::currentUser()->unfinished()->count();

        return $this->json([
            'task_count' => [
                'finished' => $finishedTasks,
                'unfinished' => $unfinishedTasks
            ],
            'tasks' => $tasks
        ], 'List of tasks');
    }

    public function finish(Task $task, bool $finished)
    {
        $task->finished = $finished;
        $task->save();

        return $this->json([], 'Finish state updated');
    }

    public function delete(Task $task)
    {
        $task->delete();
        return $this->json([], 'Task deleted');
    }

    /**
     * Get the progress of tasks for the current week.
     */
    public function weeksProgress()
    {
        $initializeWeek = function (): array {
            return array_map(function () {
                return false;
            }, range(0, 6));
        };

        $startOfWeek = Carbon::now()->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SATURDAY);

        $progress = TaskProgress::with(['task'])
            ->select(['*', \DB::raw('DAYOFWEEK(created_at) as day_of_week')])
            ->currentUser()
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])->get();

        $processedTasks = [];
        $listFinishedTasks = [];

        foreach ($progress as $taskProgress) {
            if ($taskProgress['task']) {
                $taskId = $taskProgress['task']->id;

                if ($taskProgress['task']['finished']) {
                    $listFinishedTasks[$taskId] = true;
                }

                if (!isset($processedTasks[$taskId])) {
                    $processedTasks[$taskId] = $initializeWeek();
                }

                $dayOfWeek = $taskProgress['day_of_week'];
                $processedTasks[$taskId][$dayOfWeek] = true;
            }
        }

        $tasksToShow = Task::currentUser()->unfinished()->orWhereIn('id', array_keys($listFinishedTasks))->get();

        foreach ($tasksToShow as $task) {
            if (!isset($processedTasks[$task->id])) {
                $processedTasks[$task->id] = $initializeWeek();
            }
        }

        return $this->json(['progress' => $processedTasks, 'tasks' => $tasksToShow], 'Progress this week');
    }

}
