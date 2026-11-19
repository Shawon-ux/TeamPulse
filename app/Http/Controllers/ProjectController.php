<?php
namespace App\Http\Controllers;

use App\Models\{ActivityLog,Project,Task,Team};
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Team $team)
    {
        $projects = $team->projects()->withCount(['tasks', 'tasks as done_count' => fn ($q) => $q->where('status', 'done')])->latest()->get();
        return view('projects.index', compact('projects'));
    }

    public function store(Request $r, Team $team)
    {
        $d = $r->validate(['name' => 'required|string|max:100', 'description' => 'nullable|string|max:500', 'color' => 'nullable|in:indigo,emerald,rose,amber,sky,violet']);
        if (!$team->within('projects', $team->projects()->count())) {
            return back()->with('error', 'Project limit reached on the '.$team->planConfig()['name'].' plan. Upgrade to add more.');
        }
        $p = $team->projects()->create($d + ['color' => $d['color'] ?? 'indigo']);
        ActivityLog::record('project.created', $team, ['project' => $p->name]);
        return redirect()->route('w.projects.show', [$team, $p])->with('success', 'Project created.');
    }

    public function show(Request $r, Team $team, Project $project)
    {
        abort_unless($project->team_id === $team->id, 404);
        $tasks = $project->tasks()->with('assignee')->withCount('comments')->get()->groupBy('status');
        $members = $team->members()->orderBy('name')->get();
        return view('projects.show', compact('project', 'tasks', 'members'));
    }

    public function destroy(Team $team, Project $project)
    {
        abort_unless($project->team_id === $team->id, 404);
        ActivityLog::record('project.deleted', $team, ['project' => $project->name]);
        $project->delete();
        return redirect()->route('w.projects.index', $team)->with('success', 'Project deleted.');
    }
}
