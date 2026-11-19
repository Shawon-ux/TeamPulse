<?php
namespace App\Http\Controllers;

use App\Mail\InvitationMail;
use App\Models\{ActivityLog,Invitation,Team,User};
use App\Notifications\AppNotification;
use App\Support\Members;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,Mail,URL};
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function index(Team $team)
    {
        return view('members.index', [
            'members' => $team->members()->orderBy('name')->get(),
            'invitations' => $team->invitations()->whereNull('accepted_at')->where('expires_at', '>', now())->latest()->get(),
        ]);
    }

    public function invite(Request $r, Team $team)
    {
        $d = $r->validate(['email' => 'required|email', 'role' => 'required|in:admin,member,guest']);
        if (!$team->within('members', $team->members()->count() + $team->invitations()->whereNull('accepted_at')->where('expires_at', '>', now())->count())) {
            return back()->with('error', 'Member limit reached for the '.$team->planConfig()['name'].' plan. Upgrade to invite more.');
        }
        if ($team->members()->where('email', $d['email'])->exists()) return back()->with('error', 'That person is already a member.');

        $inv = Invitation::create(['team_id' => $team->id, 'invited_by' => $r->user()->id, 'email' => $d['email'],
            'role' => $d['role'], 'token' => Str::random(48), 'expires_at' => now()->addDays(7)]);
        $url = URL::temporarySignedRoute('invitations.accept', $inv->expires_at, ['token' => $inv->token]);
        Mail::to($d['email'])->queue(new InvitationMail($inv, $url));
        ActivityLog::record('member.invited', $team, ['email' => $d['email'], 'role' => $d['role']]);
        return back()->with('success', 'Invitation sent to '.$d['email'])->with('invite_url', $url);
    }

    public function revoke(Team $team, Invitation $invitation)
    {
        abort_unless($invitation->team_id === $team->id, 404);
        $invitation->delete();
        return back()->with('success', 'Invitation revoked.');
    }

    public function accept(Request $r, string $token)
    {
        // Signed URL is verified by middleware 'signed'
        $inv = Invitation::where('token', $token)->with('team')->firstOrFail();
        if (!$inv->isUsable()) return redirect()->route('home')->with('error', 'This invitation is no longer valid.');
        session(['invite_token' => $token]);
        if (Auth::check()) {
            if (strcasecmp(Auth::user()->email, $inv->email) !== 0) {
                session()->forget('invite_token');
                return redirect()->route('home')->with('error', 'This invitation was sent to '.$inv->email.'. Sign in with that account.');
            }
            $team = Members::acceptPending(Auth::user());
            return $team ? redirect()->route('w.dashboard', $team)->with('success', "You joined {$team->name}.") : redirect()->route('home');
        }
        $exists = User::where('email', $inv->email)->exists();
        return redirect()->route($exists ? 'login' : 'register')->with('success', "Join {$inv->team->name}: ".($exists ? 'sign in' : 'create your account').' to accept.');
    }

    public function updateRole(Request $r, Team $team, User $user)
    {
        $d = $r->validate(['role' => 'required|in:admin,member,guest']);
        $role = $user->roleIn($team);
        abort_if($role === 'owner', 403, 'The owner role cannot be changed.');
        abort_if($role === 'admin' && $r->attributes->get('role') !== 'owner', 403, 'Only the owner can change an admin.');
        $team->members()->updateExistingPivot($user->id, ['role' => $d['role']]);
        ActivityLog::record('member.role_changed', $team, ['member' => $user->email, 'from' => $role, 'to' => $d['role']]);
        $user->notify(new AppNotification('Role updated', "Your role in {$team->name} is now {$d['role']}.", route('w.dashboard', $team), 'shield'));
        return back()->with('success', 'Role updated.');
    }

    public function remove(Request $r, Team $team, User $user)
    {
        $role = $user->roleIn($team);
        abort_if($role === 'owner', 403, 'The owner cannot be removed.');
        abort_if($role === 'admin' && $r->attributes->get('role') !== 'owner', 403, 'Only the owner can remove an admin.');
        $team->members()->detach($user->id);
        if ($user->current_team_id === $team->id) $user->forceFill(['current_team_id' => null])->save();
        ActivityLog::record('member.removed', $team, ['member' => $user->email]);
        return back()->with('success', 'Member removed.');
    }

    public function leave(Request $r, Team $team)
    {
        abort_if($r->attributes->get('role') === 'owner', 403, 'Owners cannot leave. Delete the workspace or transfer ownership.');
        $team->members()->detach($r->user()->id);
        ActivityLog::record('member.left', $team);
        return redirect()->route('home')->with('success', 'You left the workspace.');
    }
}
