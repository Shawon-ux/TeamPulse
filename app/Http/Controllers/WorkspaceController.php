<?php
namespace App\Http\Controllers;

use App\Models\{ActivityLog,Task,Team};
use App\Support\Members;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    public function home(Request $r)
    {
        $u = $r->user();
        if (!$r->user()) return view('welcome');
        if ($u->is_super_admin && $u->teams()->count() === 0) return redirect()->route('admin.dashboard');
        $team = $u->currentTeam && $u->roleIn($u->currentTeam) ? $u->currentTeam : $u->teams()->first();
        return $team ? redirect()->route('w.dashboard', $team) : redirect()->route('workspaces.create');
    }

    public function index(Request $r) { return view('workspaces.index', ['teams' => $r->user()->teams()->withCount('members')->get()]); }
    public function create() { return view('workspaces.create'); }

    public function store(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:80']);
        $team = Members::createWorkspace($r->user(), $d['name']);
        return redirect()->route('w.dashboard', $team)->with('success', 'Workspace created.');
    }

    public function dashboard(Request $r, Team $team)
    {
        $tasks = Task::whereHas('project', fn ($q) => $q->where('team_id', $team->id));
        $byStatus = (clone $tasks)->selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');
        $stats = [
            'projects' => $team->projects()->count(),
            'members' => $team->members()->count(),
            'tasks' => $byStatus->sum(),
            'done' => $byStatus['done'] ?? 0,
        ];
        $mine = (clone $tasks)->where('assigned_to', $r->user()->id)->where('status', '!=', 'done')->with('project')->orderBy('due_date')->limit(6)->get();
        $overdue = (clone $tasks)->where('status', '!=', 'done')->whereDate('due_date', '<', now())->count();
        $activity = $team->activityLogs()->with('user')->latest()->limit(8)->get();
        return view('dashboard', compact('stats', 'byStatus', 'mine', 'overdue', 'activity'));
    }

    public function settings(Team $team) { return view('workspaces.settings'); }

    public function update(Request $r, Team $team)
    {
        $d = $r->validate(['name' => 'required|string|max:80']);
        $team->update($d);
        ActivityLog::record('workspace.updated', $team, $d);
        return back()->with('success', 'Workspace updated.');
    }

    public function destroy(Request $r, Team $team)
    {
        $r->validate(['confirm' => 'required|in:'.$team->slug]);
        ActivityLog::record('workspace.deleted', null, ['name' => $team->name]);
        \App\Models\User::where('current_team_id', $team->id)->update(['current_team_id' => null]);
        $team->delete();
        return redirect()->route('home')->with('success', 'Workspace deleted.');
    }
}
