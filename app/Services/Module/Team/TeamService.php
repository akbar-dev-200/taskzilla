<?php

namespace App\Services\Module\Team;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamService
{
    public function teamList(Request $request, $per_page)
    {
        return Team::query()->paginate($per_page ?? 10);
    }

    public function teamCreate()
    {
        // To be implemented
    }
}