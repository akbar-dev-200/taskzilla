<?php

namespace App\Http\Controllers\Team;

use App\Http\Requests\Team\TeamRequest;
use App\Http\Controllers\Controller;
use App\Services\Module\Team\TeamService;

class TeamController extends Controller
{
    protected TeamService $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    public function teamList(TeamRequest $request)
    {
        try {
            $teamList = $this->teamService->teamList($request, $request->per_page);
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Team list fetched successfully',
                    'data' => $teamList,
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Team list fetched failed because of: ' + $th->getMessage(),
                    'data' => null,
                ],
                status: 500
            );
        }
    }
}
