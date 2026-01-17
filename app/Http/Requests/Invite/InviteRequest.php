<?php

namespace App\Http\Requests\Invite;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller/service layer
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'team_id' => ['required', 'exists:teams,id'],
            'invitations' => ['required', 'array', 'min:1', 'max:50'], // Max 50 invites at once
            'invitations.*.email' => [
                'required',
                'email',
                'max:255',
            ],
            'invitations.*.role' => [
                'required',
                Rule::enum(UserRole::class),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'team_id.required' => 'Team ID is required',
            'team_id.exists' => 'The selected team does not exist',
            'invitations.required' => 'At least one invitation is required',
            'invitations.array' => 'Invitations must be an array',
            'invitations.min' => 'At least one invitation is required',
            'invitations.max' => 'You can send up to 50 invitations at once',
            'invitations.*.email.required' => 'Email is required for each invitation',
            'invitations.*.email.email' => 'Each email must be a valid email address',
            'invitations.*.role.required' => 'Role is required for each invitation',
        ];
    }
}
