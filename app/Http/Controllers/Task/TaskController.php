<?php

namespace App\Http\Controllers\Task;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\TaskRequest;
use App\Models\Task;
use App\Services\Module\Task\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of tasks for a team
     *
     * @param TaskRequest $request
     * @param int $teamId
     * @return JsonResponse
     */
    public function taskList(TaskRequest $request, int $teamId): JsonResponse
    {
        try {
            $filters = $request->validated();
            $tasks = $this->taskService->getTeamTasks(
                $teamId,
                $filters,
                $filters['per_page'] ?? 10
            );

            return response()->json([
                'success' => true,
                'message' => 'Tasks retrieved successfully',
                'data' => $tasks,
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Failed to fetch tasks list', [
                'error' => $th->getMessage(),
                'team_id' => $teamId,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tasks',
                'error' => config('app.debug') ? $th->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Display tasks assigned to the authenticated user
     *
     * @param TaskRequest $request
     * @return JsonResponse
     */
    public function myTask(TaskRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $tasks = $this->taskService->getUserTasks(
                $request->user(),
                $filters,
                $filters['per_page'] ?? 10
            );

            return response()->json([
                'success' => true,
                'message' => 'Your tasks retrieved successfully',
                'data' => $tasks,
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Failed to fetch user tasks', [
                'error' => $th->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve your tasks',
                'error' => config('app.debug') ? $th->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Store a newly created task
     *
     * @param TaskRequest $request
     * @return JsonResponse
     */
    public function createTask(TaskRequest $request): JsonResponse
    {
        try {
            $task = $this->taskService->createTask(
                $request->validated(),
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => $task,
            ], 201);
        } catch (\Throwable $th) {
            Log::error('Failed to create task', [
                'error' => $th->getMessage(),
                'user_id' => $request->user()->id,
                'data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create task',
                'error' => config('app.debug') ? $th->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Display the specified task with details
     *
     * @param TaskRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function taskShow(TaskRequest $request, Task $task): JsonResponse
    {
        try {
            $taskDetails = $this->taskService->getTaskDetails($task);

            return response()->json([
                'success' => true,
                'message' => 'Task details retrieved successfully',
                'data' => $taskDetails,
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Failed to fetch task details', [
                'error' => $th->getMessage(),
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve task details',
                'error' => config('app.debug') ? $th->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Update the specified task
     *
     * @param TaskRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function updateTask(TaskRequest $request, Task $task): JsonResponse
    {
        try {
            $updatedTask = $this->taskService->updateTask(
                $task,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'data' => $updatedTask,
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Failed to update task', [
                'error' => $th->getMessage(),
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
                'data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update task',
                'error' => config('app.debug') ? $th->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Update the task status
     *
     * @param TaskRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function updateTaskStatus(TaskRequest $request, Task $task): JsonResponse
    {
        try {
            $validated = $request->validated();
            $updatedTask = $this->taskService->updateTaskStatus(
                $task,
                TaskStatus::from($validated['status'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully',
                'data' => $updatedTask,
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Failed to update task status', [
                'error' => $th->getMessage(),
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
                'data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update task status',
                'error' => config('app.debug') ? $th->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Remove the specified task
     *
     * @param TaskRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function deleteTask(TaskRequest $request, Task $task): JsonResponse
    {
        try {
            $this->taskService->deleteTask($task);

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Failed to delete task', [
                'error' => $th->getMessage(),
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task',
                'error' => config('app.debug') ? $th->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Assign users to a task
     *
     * @param TaskRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function assignTaskUsers(TaskRequest $request, Task $task): JsonResponse
    {
        try {
            $validated = $request->validated();
            $this->taskService->assignUsers($task, $validated['user_ids']);

            $task->load(['assignees:id,name,email']);

            return response()->json([
                'success' => true,
                'message' => 'Users assigned to task successfully',
                'data' => [
                    'task_id' => $task->id,
                    'task_uuid' => $task->uuid,
                    'assignees' => $task->assignees,
                ],
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Failed to assign users to task', [
                'error' => $th->getMessage(),
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
                'data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign users to task',
                'error' => config('app.debug') ? $th->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Remove users from a task
     *
     * @param TaskRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function removeTaskAssignees(TaskRequest $request, Task $task): JsonResponse
    {
        try {
            $validated = $request->validated();
            $this->taskService->removeUsers($task, $validated['user_ids']);

            $task->load(['assignees:id,name,email']);

            return response()->json([
                'success' => true,
                'message' => 'Users removed from task successfully',
                'data' => [
                    'task_id' => $task->id,
                    'task_uuid' => $task->uuid,
                    'assignees' => $task->assignees,
                ],
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Failed to remove users from task', [
                'error' => $th->getMessage(),
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
                'data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove users from task',
                'error' => config('app.debug') ? $th->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Get task statistics for a team
     *
     * @param int $teamId
     * @return JsonResponse
     */
    public function taskStatistics(int $teamId): JsonResponse
    {
        try {
            $stats = $this->taskService->getTaskStatistics($teamId);

            return response()->json([
                'success' => true,
                'message' => 'Task statistics retrieved successfully',
                'data' => $stats,
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Failed to fetch task statistics', [
                'error' => $th->getMessage(),
                'team_id' => $teamId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve task statistics',
                'error' => config('app.debug') ? $th->getMessage() : 'An error occurred',
            ], 500);
        }
    }
}
