<?php

namespace App\Http\Controllers\Invite;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invite\AcceptInviteRequest;
use App\Http\Requests\Invite\InviteRequest;
use App\Models\Team;
use App\Services\Module\Invite\InviteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InviteController extends Controller
{
    protected InviteService $inviteService;

    public function __construct(InviteService $inviteService)
    {
        $this->inviteService = $inviteService;
    }

    /**
     * Send invitations to multiple users.
     *
     * @param InviteRequest $request
     * @return JsonResponse
     */
    public function sendInvitations(InviteRequest $request): JsonResponse
    {
        try {
            $team = Team::findOrFail($request->team_id);

            // Check if user has permission to invite (team admin/creator)
            $user = $request->user();
            if ($team->created_by !== $user->id && !$this->isTeamAdmin($team, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to invite users to this team',
                ], 403);
            }

            $results = $this->inviteService->sendInvitations(
                $team,
                $request->invitations,
                $user
            );

            $successCount = collect($results)->where('success', true)->count();
            $failureCount = collect($results)->where('success', false)->count();

            return response()->json([
                'success' => true,
                'message' => "Successfully sent {$successCount} invitation(s). {$failureCount} failed.",
                'data' => [
                    'results' => $results,
                    'summary' => [
                        'total' => count($results),
                        'successful' => $successCount,
                        'failed' => $failureCount,
                    ],
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send invitations: ' . $th->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Accept an invitation.
     *
     * @param AcceptInviteRequest $request
     * @return JsonResponse
     */
    public function acceptInvitation(AcceptInviteRequest $request): JsonResponse
    {
        try {
            $user = $request->user(); // Can be null for guest users
            $result = $this->inviteService->acceptInvitation($request->token, $user);

            if (!$result['success']) {
                $statusCode = isset($result['requires_registration']) ? 422 : 400;
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'data' => $result,
                ], $statusCode);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'team' => $result['team'],
                    'role' => $result['role'],
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept invitation: ' . $th->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Revoke an invitation.
     *
     * @param Request $request
     * @param int $inviteId
     * @return JsonResponse
     */
    public function revokeInvitation(Request $request, int $inviteId): JsonResponse
    {
        try {
            $result = $this->inviteService->revokeInvitation($inviteId, $request->user());

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke invitation: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all invitations for a team.
     *
     * @param Request $request
     * @param int $teamId
     * @return JsonResponse
     */
    public function getTeamInvitations(Request $request, int $teamId): JsonResponse
    {
        try {
            $team = Team::findOrFail($teamId);
            $user = $request->user();

            // Check if user has permission to view invitations
            if ($team->created_by !== $user->id && !$this->isTeamAdmin($team, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view invitations for this team',
                ], 403);
            }

            $status = $request->query('status'); // Optional filter by status
            $invitations = $this->inviteService->getTeamInvitations($team, $status);

            return response()->json([
                'success' => true,
                'message' => 'Team invitations fetched successfully',
                'data' => $invitations,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch invitations: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending invitations for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getMyPendingInvitations(Request $request): JsonResponse
    {
        try {
            $invitations = $this->inviteService->getUserPendingInvitations($request->user()->email);

            return response()->json([
                'success' => true,
                'message' => 'Pending invitations fetched successfully',
                'data' => $invitations,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending invitations: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if user is an admin of the team.
     *
     * @param Team $team
     * @param \App\Models\User $user
     * @return bool
     */
    private function isTeamAdmin(Team $team, $user): bool
    {
        $membership = $team->members()->where('user_id', $user->id)->first();
        return $membership && $membership->pivot->role->value === 'admin';
    }
}
