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
            // We accept Team UUIDs externally (route model binding uses uuid too).
            // Keep the field name `team_id` for backward compatibility with clients.
            'team_id' => ['required', 'uuid', Rule::exists('teams', 'uuid')],
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
     * Support a simpler payload:
     * {
     *   "team_id": "...uuid...",
     *   "emails": ["a@x.com", "b@x.com"],
     *   "role": "member"  // optional, defaults to "member"
     * }
     *
     * by transforming it into the canonical format:
     * {
     *   "invitations": [{"email":"a@x.com","role":"member"}, ...]
     * }
     */
    protected function prepareForValidation(): void
    {
        $hasInvitations = $this->has('invitations');
        $emails = $this->input('emails');
        $role = $this->input('role', 'member'); // Default to 'member' if not provided

        if (!$hasInvitations && is_array($emails) && !empty($emails)) {
            $this->merge([
                'invitations' => array_map(
                    fn ($email) => ['email' => $email, 'role' => $role],
                    $emails
                ),
            ]);
        }
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
            'team_id.uuid' => 'Team ID must be a valid UUID',
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
