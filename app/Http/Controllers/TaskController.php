<?php
namespace App\Http\Controllers;

use App\Models\{ActivityLog,Attachment,Project,Task,TaskComment,Team,User};
use App\Notifications\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    protected function scoped(Team $team, Task $task): Task
    {
        abort_unless($task->project->team_id === $team->id, 404);
        return $task;
    }

    protected function notifyAssignee(Task $task, Team $team, Request $r): void
    {
        if ($task->assigned_to && $task->assigned_to !== $r->user()->id) {
            User::find($task->assigned_to)?->notify(new AppNotification('Task assigned to you',
                "{$r->user()->name} assigned “{$task->title}”", route('w.projects.show', [$team, $task->project_id]), 'check'));
        }
    }

    public function store(Request $r, Team $team, Project $project)
    {
        abort_unless($project->team_id === $team->id, 404);
        $d = $r->validate(['title' => 'required|string|max:150', 'description' => 'nullable|string|max:2000',
            'status' => 'nullable|in:'.implode(',', array_keys(Task::STATUSES)), 'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date', 'assigned_to' => 'nullable|exists:team_user,user_id']);
        if (!empty($d['assigned_to']) && !User::find($d['assigned_to'])?->roleIn($team)) unset($d['assigned_to']);
        $d['status'] ??= 'todo'; $d['priority'] ??= 'medium';
        $d['position'] = (int) $project->tasks()->where('status', $d['status'])->max('position') + 1;
        $task = $project->tasks()->create($d + ['created_by' => $r->user()->id]);
        $this->notifyAssignee($task, $team, $r);
        ActivityLog::record('task.created', $team, ['task' => $task->title]);
        return back()->with('success', 'Task added.');
    }

    public function show(Team $team, Task $task)
    {
        $this->scoped($team, $task);
        $task->load(['assignee', 'comments', 'attachments.user']);
        return view('projects.task', ['task' => $task, 'members' => $team->members()->orderBy('name')->get()]);
    }

    public function update(Request $r, Team $team, Task $task)
    {
        $this->scoped($team, $task);
        $d = $r->validate(['title' => 'required|string|max:150', 'description' => 'nullable|string|max:2000',
            'status' => 'required|in:'.implode(',', array_keys(Task::STATUSES)), 'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date', 'assigned_to' => 'nullable|exists:users,id']);
        $prev = $task->assigned_to;
        $task->update($d);
        if ($task->assigned_to !== $prev) $this->notifyAssignee($task, $team, $r);
        return back()->with('success', 'Task updated.');
    }

    /** Drag-and-drop endpoint (JSON). */
    public function move(Request $r, Team $team, Task $task)
    {
        $this->scoped($team, $task);
        $d = $r->validate(['status' => 'required|in:'.implode(',', array_keys(Task::STATUSES)), 'ids' => 'required|array', 'ids.*' => 'integer']);
        $task->update(['status' => $d['status']]);
        foreach ($d['ids'] as $i => $id) {
            Task::where('id', $id)->whereHas('project', fn ($q) => $q->where('team_id', $team->id))->update(['position' => $i]);
        }
        return response()->json(['ok' => true]);
    }

    public function destroy(Team $team, Task $task)
    {
        $this->scoped($team, $task);
        $pid = $task->project_id;
        foreach ($task->attachments as $a) Storage::disk('local')->delete($a->path);
        $task->delete();
        return redirect()->route('w.projects.show', [$team, $pid])->with('success', 'Task deleted.');
    }

    public function comment(Request $r, Team $team, Task $task)
    {
        $this->scoped($team, $task);
        $d = $r->validate(['body' => 'required|string|max:2000']);
        TaskComment::create(['task_id' => $task->id, 'user_id' => $r->user()->id, 'body' => $d['body']]);
        foreach (\App\Support\Mentions::users($d['body'], $team) as $u) {
            if ($u->id !== $r->user()->id) $u->notify(new AppNotification('You were mentioned', "{$r->user()->name} on “{$task->title}”", route('w.tasks.show', [$team, $task]), 'at-sign'));
        }
        return back();
    }

    public function attach(Request $r, Team $team, Task $task)
    {
        $this->scoped($team, $task);
        $r->validate(['file' => 'required|file|max:5120|mimes:jpg,jpeg,png,gif,webp,pdf,txt,csv,doc,docx,xls,xlsx,zip']);
        $f = $r->file('file');
        $max = $team->limit('storage_mb') * 1024 * 1024;
        if (Attachment::where('team_id', $team->id)->sum('size') + $f->getSize() > $max) return back()->with('error', 'Storage limit reached for your plan.');
        Attachment::create(['team_id' => $team->id, 'task_id' => $task->id, 'user_id' => $r->user()->id,
            'original_name' => $f->getClientOriginalName(), 'path' => $f->store("attachments/{$team->id}", 'local'),
            'mime' => $f->getMimeType(), 'size' => $f->getSize()]);
        return back()->with('success', 'File uploaded.');
    }

    public function download(Team $team, Attachment $attachment)
    {
        abort_unless($attachment->team_id === $team->id, 404);
        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }
}
