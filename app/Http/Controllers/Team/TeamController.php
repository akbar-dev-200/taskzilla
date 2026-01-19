<?php

namespace App\Http\Controllers\Team;

use App\Http\Requests\Team\TeamRequest;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Services\Module\Team\TeamService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    use ApiResponse;

    protected TeamService $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    /**
     * Display a listing of teams the user belongs to.
     *
     * @param TeamRequest $request
     * @return JsonResponse
     */
    public function teamList(TeamRequest $request): JsonResponse
    {
        try {
            $teams = $this->teamService->getUserTeams(
                $request->user(),
                $request->validated('per_page')
            );

            return $this->paginatedResponse($teams, 'Teams retrieved successfully');
        } catch (\Throwable $th) {
            Log::error('Failed to fetch teams list', [
                'error' => $th->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return $this->errorResponse(
                'Failed to retrieve teams',
                500,
                config('app.debug') ? $th->getMessage() : null
            );
        }
    }

    /**
     * Store a newly created team.
     *
     * @param TeamRequest $request
     * @return JsonResponse
     */
    public function store(TeamRequest $request): JsonResponse
    {
        try {
            $team = $this->teamService->createTeam(
                $request->validated(),
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Team created successfully',
                'data' => $team,
            ], 201);
        } catch (\Throwable $th) {
            Log::error('Failed to create team', [
                'error' => $th->getMessage(),
                'user_id' => $request->user()->id,
                'data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create team',
                'error' => config('app.debug') ? $th->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Display the specified team with members and tasks overview.
     *
     * @param TeamRequest $request
     * @param Team $team
     * @return JsonResponse
     */
    public function teamShow(TeamRequest $request, Team $team): JsonResponse
    {
        try {
            $teamDetails = $this->teamService->getTeamDetails($team);

            return $this->successResponse($teamDetails, 'Team details retrieved successfully');
        } catch (\Throwable $th) {
            Log::error('Failed to fetch team details', [
                'error' => $th->getMessage(),
                'team_id' => $team->id,
                'user_id' => $request->user()->id,
            ]);

            return $this->errorResponse(
                'Failed to retrieve team details',
                500,
                config('app.debug') ? $th->getMessage() : null
            );
        }
    }

    /**
     * Update the specified team.
     *
     * @param TeamRequest $request
     * @param Team $team
     * @return JsonResponse
     */
    public function teamUpdate(TeamRequest $request, Team $team): JsonResponse
    {
        try {
            $updatedTeam = $this->teamService->updateTeam(
                $team,
                $request->validated()
            );

            return $this->successResponse($updatedTeam, 'Team updated successfully');
        } catch (\Throwable $th) {
            Log::error('Failed to update team', [
                'error' => $th->getMessage(),
                'team_id' => $team->id,
                'user_id' => $request->user()->id,
                'data' => $request->validated(),
            ]);

            return $this->errorResponse(
                'Failed to update team',
                500,
                config('app.debug') ? $th->getMessage() : null
            );
        }
    }

    /**
     * Remove the specified team.
     *
     * @param TeamRequest $request
     * @param Team $team
     * @return JsonResponse
     */
    public function teamDelete(TeamRequest $request, Team $team): JsonResponse
    {
        try {
            $this->teamService->deleteTeam($team);

            return $this->noContentResponse('Team deleted successfully');
        } catch (\Throwable $th) {
            Log::error('Failed to delete team', [
                'error' => $th->getMessage(),
                'team_id' => $team->id,
                'user_id' => $request->user()->id,
            ]);

            return $this->errorResponse(
                'Failed to delete team',
                500,
                config('app.debug') ? $th->getMessage() : null
            );
        }
    }
}
