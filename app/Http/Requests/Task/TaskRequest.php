<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $routeName = $this->route()->getName();

        return match ($routeName) {
            'tasks.index' => true, // Team members can list tasks
            'tasks.my-tasks' => true, // Any authenticated user
            'tasks.store' => Gate::allows('create-task', $this->input('team_id')),
            'tasks.show' => Gate::allows('view-task', $this->route('task')),
            'tasks.update' => Gate::allows('update-task', $this->route('task')),
            'tasks.update-status' => Gate::allows('update-task-status', $this->route('task')),
            'tasks.destroy' => Gate::allows('delete-task', $this->route('task')),
            'tasks.assign' => Gate::allows('manage-task-assignees', $this->route('task')),
            'tasks.remove-assignee' => Gate::allows('manage-task-assignees', $this->route('task')),
            default => true,
        };
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $routeName = $this->route()->getName();

        return match ($routeName) {
            'tasks.index', 'tasks.my-tasks' => $this->indexRules(),
            'tasks.store' => $this->storeRules(),
            'tasks.update' => $this->updateRules(),
            'tasks.update-status' => $this->updateStatusRules(),
            'tasks.assign' => $this->assignRules(),
            'tasks.remove-assignee' => $this->removeAssigneeRules(),
            default => [],
        };
    }

    /**
     * Validation rules for index/list endpoint
     *
     * @return array
     */
    protected function indexRules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
            'priority' => ['nullable', Rule::enum(TaskPriority::class)],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'assigned_by' => ['nullable', 'integer', 'exists:users,id'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'overdue' => ['nullable', 'boolean'],
            'due_soon' => ['nullable', 'boolean'],
            'sort_by' => ['nullable', 'string', Rule::in(['created_at', 'due_date', 'priority', 'status', 'title'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Validation rules for store endpoint
     *
     * @return array
     */
    protected function storeRules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
            'priority' => ['nullable', Rule::enum(TaskPriority::class)],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'assignee_ids' => ['nullable', 'array'],
            'assignee_ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    /**
     * Validation rules for update endpoint
     *
     * @return array
     */
    protected function updateRules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['sometimes', Rule::enum(TaskStatus::class)],
            'priority' => ['sometimes', Rule::enum(TaskPriority::class)],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * Validation rules for update status endpoint
     *
     * @return array
     */
    protected function updateStatusRules(): array
    {
        return [
            'status' => ['required', Rule::enum(TaskStatus::class)],
        ];
    }

    /**
     * Validation rules for assign users endpoint
     *
     * @return array
     */
    protected function assignRules(): array
    {
        return [
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => [
                'required',
                'integer',
                'exists:users,id',
                // Validate user is member of the task's team
                function ($attribute, $value, $fail) {
                    $task = $this->route('task');
                    $user = \App\Models\User::find($value);
                    
                    if ($user && !$user->teams()->where('teams.id', $task->team_id)->exists()) {
                        $fail('The selected user must be a member of the task\'s team.');
                    }
                },
            ],
        ];
    }

    /**
     * Validation rules for remove assignee endpoint
     *
     * @return array
     */
    protected function removeAssigneeRules(): array
    {
        return [
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'task title',
            'description' => 'task description',
            'status' => 'task status',
            'priority' => 'task priority',
            'due_date' => 'due date',
            'team_id' => 'team',
            'assignee_ids' => 'assignees',
            'user_ids' => 'users',
            'per_page' => 'items per page',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The task title is required.',
            'title.min' => 'The task title must be at least :min characters.',
            'title.max' => 'The task title must not exceed :max characters.',
            'due_date.after_or_equal' => 'The due date must be today or a future date.',
            'team_id.required' => 'Please select a team for this task.',
            'team_id.exists' => 'The selected team does not exist.',
            'assignee_ids.array' => 'Assignees must be provided as an array.',
            'assignee_ids.*.exists' => 'One or more selected assignees do not exist.',
            'user_ids.required' => 'Please provide at least one user.',
            'user_ids.array' => 'Users must be provided as an array.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from title if present
        if ($this->has('title')) {
            $this->merge([
                'title' => trim($this->title),
            ]);
        }

        // Set default per_page if not provided
        if ($this->routeIs('tasks.index', 'tasks.my-tasks') && !$this->has('per_page')) {
            $this->merge([
                'per_page' => 10,
            ]);
        }
    }
}
