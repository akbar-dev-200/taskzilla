<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use App\Services\Common\Avatar\AvatarService;
use App\Enums\UserRole;
use App\Mail\WelcomeMail;

class RegisteredUserController extends Controller
{
    private AvatarService $avatarService;

    public function __construct(AvatarService $avatarService)
    {
        $this->avatarService = $avatarService;
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'avatar' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => UserRole::ADMIN,
        ]);

        if ($request->hasFile('avatar')) {
            $this->avatarService->generateAndUploadAvatar($user, $request->file('avatar'));
            $user->refresh();
        } else {
            // Auto-generate a default avatar (e.g., John Doe -> JD) when no file is provided.
            $this->avatarService->generateDefaultAvatar($user);
            $user->refresh();
        }

        event(new Registered($user));

        // Dispatch welcome email from controller (as requested)
        try {
            Mail::to($user->email)->send(new WelcomeMail($user));
        } catch (\Throwable $e) {
            // Do not fail registration if mail is misconfigured.
            // You can inspect logs to debug mail transport.
            logger()->warning('Welcome email failed to send', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        // Create Sanctum token for API authentication
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'uuid' => $user->uuid,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'avatar' => $user->avatar,
                    'avatar_url' => $this->avatarService->url($user),
                ],
                'token' => $token,
            ],
        ], 201);
    }
}
