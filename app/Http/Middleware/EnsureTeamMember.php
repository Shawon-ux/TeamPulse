<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

/** Global tenant scoping: resolves the workspace from the URL and verifies membership. */
class EnsureTeamMember
{
    public function handle(Request $request, Closure $next)
    {
        $team = $request->route('team');
        $user = $request->user();
        $role = $user->roleIn($team);
        abort_unless($role, 403, 'You are not a member of this workspace.');

        if ($user->current_team_id !== $team->id) {
            $user->forceFill(['current_team_id' => $team->id])->save();
        }
        app()->instance('currentTeam', $team);
        $request->attributes->set('role', $role);
        View::share(['team' => $team, 'role' => $role]);
        return $next($request);
    }
}
