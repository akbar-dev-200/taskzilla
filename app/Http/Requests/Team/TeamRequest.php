<?php

namespace App\Http\Requests\Team;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class TeamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $routeName = $this->route()->getName();

        // Check authorization based on route name
        return match ($routeName) {
            'team.index' => Gate::allows('view-team-list'),
            'team.store' => Gate::allows('create-team'),
            'team.show' => Gate::allows('view-team', $this->route('team')),
            'team.update' => Gate::allows('update-team', $this->route('team')),
            'team.destroy' => Gate::allows('delete-team', $this->route('team')),
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
            'team.index' => $this->indexRules(),
            'team.store' => $this->storeRules(),
            'team.update' => $this->updateRules(),
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
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
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
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('teams', 'name'),
            ],
        ];
    }

    /**
     * Validation rules for update endpoint
     *
     * @return array
     */
    protected function updateRules(): array
    {
        $teamId = $this->route('team')->id ?? null;

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('teams', 'name')->ignore($teamId),
            ],
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
            'name' => 'team name',
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
            'name.required' => 'The team name is required.',
            'name.string' => 'The team name must be a valid text.',
            'name.min' => 'The team name must be at least :min characters.',
            'name.max' => 'The team name must not exceed :max characters.',
            'name.unique' => 'A team with this name already exists.',
            'per_page.integer' => 'The items per page must be a number.',
            'per_page.min' => 'The items per page must be at least :min.',
            'per_page.max' => 'The items per page must not exceed :max.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from name if present
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }

        // Set default per_page if not provided
        if (!$this->has('per_page')) {
            $this->merge([
                'per_page' => 10,
            ]);
        }
    }
}
