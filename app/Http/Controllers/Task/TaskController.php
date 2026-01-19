<?php

namespace App\Http\Controllers\Task;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\TaskRequest;
use App\Models\Task;
use App\Services\Module\Task\TaskService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    use ApiResponse;

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

            return $this->paginatedResponse($tasks, 'Tasks retrieved successfully');
        } catch (\Throwable $th) {
            Log::error('Failed to fetch tasks list', [
                'error' => $th->getMessage(),
                'team_id' => $teamId,
                'user_id' => $request->user()->id,
            ]);

            return $this->errorResponse(
                'Failed to retrieve tasks',
                500,
                config('app.debug') ? $th->getMessage() : null
            );
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

            return $this->paginatedResponse($tasks, 'Your tasks retrieved successfully');
        } catch (\Throwable $th) {
            Log::error('Failed to fetch user tasks', [
                'error' => $th->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return $this->errorResponse(
                'Failed to retrieve your tasks',
                500,
                config('app.debug') ? $th->getMessage() : null
            );
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

            return $this->createdResponse($task, 'Task created successfully');
        } catch (\Throwable $th) {
            Log::error('Failed to create task', [
                'error' => $th->getMessage(),
                'user_id' => $request->user()->id,
                'data' => $request->validated(),
            ]);

            return $this->errorResponse(
                'Failed to create task',
                500,
                config('app.debug') ? $th->getMessage() : null
            );
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

            return $this->successResponse($taskDetails, 'Task details retrieved successfully');
        } catch (\Throwable $th) {
            Log::error('Failed to fetch task details', [
                'error' => $th->getMessage(),
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
            ]);

            return $this->errorResponse(
                'Failed to retrieve task details',
                500,
                config('app.debug') ? $th->getMessage() : null
            );
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

            return $this->successResponse($updatedTask, 'Task updated successfully');
        } catch (\Throwable $th) {
            Log::error('Failed to update task', [
                'error' => $th->getMessage(),
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
                'data' => $request->validated(),
            ]);

            return $this->errorResponse(
                'Failed to update task',
                500,
                config('app.debug') ? $th->getMessage() : null
            );
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

            return $this->successResponse($updatedTask, 'Task status updated successfully');
        } catch (\Throwable $th) {
            Log::error('Failed to update task status', [
                'error' => $th->getMessage(),
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
                'data' => $request->validated(),
            ]);

            return $this->errorResponse(
                'Failed to update task status',
                500,
                config('app.debug') ? $th->getMessage() : null
            );
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

            return $this->noContentResponse('Task deleted successfully');
        } catch (\Throwable $th) {
            Log::error('Failed to delete task', [
                'error' => $th->getMessage(),
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
            ]);

            return $this->errorResponse(
                'Failed to delete task',
                500,
                config('app.debug') ? $th->getMessage() : null
            );
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

            return $this->successResponse([
                'task_id' => $task->id,
                'task_uuid' => $task->uuid,
                'assignees' => $task->assignees,
            ], 'Users assigned to task successfully');
        } catch (\Throwable $th) {
            Log::error('Failed to assign users to task', [
                'error' => $th->getMessage(),
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
                'data' => $request->validated(),
            ]);

            return $this->errorResponse(
                'Failed to assign users to task',
                500,
                config('app.debug') ? $th->getMessage() : null
            );
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

            return $this->successResponse([
                'task_id' => $task->id,
                'task_uuid' => $task->uuid,
                'assignees' => $task->assignees,
            ], 'Users removed from task successfully');
        } catch (\Throwable $th) {
            Log::error('Failed to remove users from task', [
                'error' => $th->getMessage(),
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
                'data' => $request->validated(),
            ]);

            return $this->errorResponse(
                'Failed to remove users from task',
                500,
                config('app.debug') ? $th->getMessage() : null
            );
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

            return $this->successResponse($stats, 'Task statistics retrieved successfully');
        } catch (\Throwable $th) {
            Log::error('Failed to fetch task statistics', [
                'error' => $th->getMessage(),
                'team_id' => $teamId,
            ]);

            return $this->errorResponse(
                'Failed to retrieve task statistics',
                500,
                config('app.debug') ? $th->getMessage() : null
            );
        }
    }
}
