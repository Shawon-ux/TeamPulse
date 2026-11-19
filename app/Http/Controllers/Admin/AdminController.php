<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{ActivityLog,Invoice,Message,Project,Task,Team,User};
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users' => User::count(), 'teams' => Team::count(), 'projects' => Project::count(), 'tasks' => Task::count(),
            'messages' => Message::count(),
            'mrr' => Team::whereIn('plan', ['pro', 'enterprise'])->get()->sum(fn ($t) => config("plans.{$t->plan}.price")),
        ];
        $plans = Team::selectRaw('plan, count(*) c')->groupBy('plan')->pluck('c', 'plan');
        $signups = User::selectRaw('date(created_at) d, count(*) c')->where('created_at', '>=', now()->subDays(13)->startOfDay())->groupBy('d')->pluck('c', 'd');
        $series = collect(range(13, 0))->map(fn ($i) => ['d' => now()->subDays($i)->format('M j'), 'c' => $signups[now()->subDays($i)->toDateString()] ?? 0]);
        return view('admin.dashboard', ['stats' => $stats, 'plans' => $plans, 'series' => $series,
            'recent' => ActivityLog::with(['user', 'team'])->latest()->limit(10)->get()]);
    }

    public function users(Request $r)
    {
        $users = User::withCount('teams')->when($r->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")))
            ->latest()->paginate(20)->withQueryString();
        return view('admin.users', compact('users'));
    }

    public function toggleSuspend(Request $r, User $user)
    {
        abort_if($user->is_super_admin || $user->id === $r->user()->id, 403, 'Cannot suspend an administrator.');
        $user->update(['is_suspended' => !$user->is_suspended]);
        if ($user->is_suspended) \DB::table('sessions')->where('user_id', $user->id)->delete();
        ActivityLog::record($user->is_suspended ? 'admin.user_suspended' : 'admin.user_restored', null, ['user' => $user->email]);
        return back()->with('success', 'User '.($user->is_suspended ? 'suspended' : 'restored').'.');
    }

    public function destroyUser(Request $r, User $user)
    {
        abort_if($user->is_super_admin || $user->id === $r->user()->id, 403);
        if (Team::where('user_id', $user->id)->exists()) return back()->with('error', 'User owns workspaces. Delete those first.');
        ActivityLog::record('admin.user_deleted', null, ['user' => $user->email]);
        $user->delete();
        return back()->with('success', 'User deleted.');
    }

    public function teams()
    {
        return view('admin.teams', ['teams' => Team::with('owner')->withCount(['members', 'projects'])->latest()->paginate(20)]);
    }

    public function setPlan(Request $r, Team $team)
    {
        $d = $r->validate(['plan' => 'required|in:free,pro,enterprise']);
        $team->update(['plan' => $d['plan'], 'stripe_status' => $d['plan'] === 'free' ? null : 'active']);
        ActivityLog::record('admin.plan_set', $team, $d);
        return back()->with('success', 'Plan updated.');
    }

    public function destroyTeam(Team $team)
    {
        ActivityLog::record('admin.workspace_deleted', null, ['name' => $team->name]);
        User::where('current_team_id', $team->id)->update(['current_team_id' => null]);
        $team->delete();
        return back()->with('success', 'Workspace deleted.');
    }

    public function logs(Request $r)
    {
        $logs = ActivityLog::with(['user', 'team'])->when($r->q, fn ($q, $s) => $q->where('action', 'like', "%$s%")->orWhere('ip_address', 'like', "%$s%")
            ->orWhere('properties', 'like', "%$s%"))->latest()->paginate(30)->withQueryString();
        return view('admin.logs', compact('logs'));
    }
}
