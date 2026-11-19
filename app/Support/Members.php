<?php
namespace App\Support;

use App\Models\{Invitation,Team,User,Channel,ActivityLog};

class Members
{
    /** Create a workspace with defaults for a user who becomes its owner. */
    public static function createWorkspace(User $user, string $name): Team
    {
        $team = Team::create(['user_id' => $user->id, 'name' => $name, 'slug' => Team::uniqueSlug($name), 'plan' => 'free']);
        $team->members()->attach($user->id, ['role' => 'owner']);
        foreach (['general', 'random'] as $c) Channel::create(['team_id' => $team->id, 'name' => $c]);
        $user->forceFill(['current_team_id' => $team->id])->save();
        ActivityLog::record('workspace.created', $team, ['name' => $name], $user);
        return $team;
    }

    /** Attach a user to the workspace of a pending (session-stored) invitation. Returns the team or null. */
    public static function acceptPending(User $user): ?Team
    {
        $token = session()->pull('invite_token');
        if (!$token) return null;
        $inv = Invitation::where('token', $token)->first();
        if (!$inv || !$inv->isUsable()) return null;
        if (strcasecmp($inv->email, $user->email) !== 0) return null;
        $inv->team->members()->syncWithoutDetaching([$user->id => ['role' => $inv->role]]);
        $inv->update(['accepted_at' => now()]);
        $user->forceFill(['current_team_id' => $inv->team_id])->save();
        ActivityLog::record('member.joined', $inv->team, ['email' => $user->email, 'role' => $inv->role], $user);
        return $inv->team;
    }
}
