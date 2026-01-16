<?php

namespace App\Http\Requests\Team;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class TeamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Only admin can create, update and delete teams.
     */
    public function authorize(): bool
    {
       if($this->route()->getName() === 'team.create') {
        return Gate::allows('create-team');
       }
       if($this->route()->getName() === 'team.delete') {
        return Gate::allows('delete-team');
       }
       return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => [
                $this->isMethod('PUT') || $this->isMethod('PATCH') ? 'sometimes' : 'required',
                'string',
                'min:2',
                'max:255',
            ],
            'lead_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ];

        return $rules;
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
            'lead_id' => 'team lead',
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
            'name.min' => 'The team name must be at least :min characters.',
            'name.max' => 'The team name must not exceed :max characters.',
            'lead_id.exists' => 'The selected team lead does not exist.',
            'lead_id.integer' => 'The team lead must be a valid user ID.',
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
    }
}
