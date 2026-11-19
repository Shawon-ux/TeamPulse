@extends('layouts.app')
@section('heading', 'Task')
@section('content')
@php $canWrite = auth()->user()->canIn($team,'write'); @endphp
<a href="{{ route('w.projects.show',[$team,$task->project_id]) }}" class="mb-4 inline-block text-sm text-indigo-600 hover:underline">← {{ $task->project->name }}</a>
<div class="grid gap-6 lg:grid-cols-3">
  <div class="space-y-6 lg:col-span-2">
    <form method="POST" action="{{ route('w.tasks.update',[$team,$task]) }}" class="card space-y-4 p-6">@csrf @method('PUT')
      <fieldset @disabled(!$canWrite) class="space-y-4">
      <div><label class="label">Title</label><input name="title" value="{{ $task->title }}" class="input"></div>
      <div><label class="label">Description</label><textarea name="description" rows="3" class="input">{{ $task->description }}</textarea></div>
      <div class="grid gap-4 sm:grid-cols-2">
        <div><label class="label">Status</label><select name="status" class="input">@foreach(\App\Models\Task::STATUSES as $k=>$l)<option value="{{ $k }}" @selected($task->status==$k)>{{ $l }}</option>@endforeach</select></div>
        <div><label class="label">Priority</label><select name="priority" class="input">@foreach(['low','medium','high','urgent'] as $p)<option @selected($task->priority==$p)>{{ $p }}</option>@endforeach</select></div>
        <div><label class="label">Due date</label><input type="date" name="due_date" value="{{ $task->due_date?->toDateString() }}" class="input"></div>
        <div><label class="label">Assignee</label><select name="assigned_to" class="input"><option value="">Unassigned</option>@foreach($members as $m)<option value="{{ $m->id }}" @selected($task->assigned_to==$m->id)>{{ $m->name }}</option>@endforeach</select></div>
      </div></fieldset>
      @if($canWrite)<div class="flex justify-between"><button class="btn-primary">Save changes</button></div>@endif
    </form>
    @if($canWrite)<form method="POST" action="{{ route('w.tasks.destroy',[$team,$task]) }}" onsubmit="return confirm('Delete this task?')">@csrf @method('DELETE')<button class="text-sm text-rose-600 hover:underline">Delete task</button></form>@endif

    <div class="card p-6"><h3 class="font-bold">Comments</h3>
      <div class="mt-4 space-y-4">@forelse($task->comments as $c)<div class="flex gap-3"><img src="{{ $c->user->avatarUrl() }}" class="h-8 w-8 rounded-full"><div class="rounded-lg bg-slate-50 px-3 py-2 text-sm"><b>{{ $c->user->name }}</b> <span class="text-xs text-slate-400">{{ $c->created_at->diffForHumans() }}</span><p class="mt-0.5">{{ $c->body }}</p></div></div>@empty<p class="text-sm text-slate-400">No comments yet.</p>@endforelse</div>
      @if($canWrite)<form method="POST" action="{{ route('w.tasks.comment',[$team,$task]) }}" class="mt-4 flex gap-2">@csrf<input name="body" required placeholder="Write a comment… use @@name to mention" class="input"><button class="btn-primary">Send</button></form>@endif
    </div>
  </div>
  <div class="card h-fit p-6"><h3 class="font-bold">Attachments</h3>
    <ul class="mt-3 space-y-2 text-sm">@forelse($task->attachments as $a)<li class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2"><a href="{{ route('w.attachments.download',[$team,$a]) }}" class="truncate text-indigo-600 hover:underline">📎 {{ $a->original_name }}</a><span class="ml-2 text-xs text-slate-400">{{ number_format($a->size/1024,0) }} KB</span></li>@empty<li class="text-slate-400">No files.</li>@endforelse</ul>
    @if($canWrite)<form method="POST" action="{{ route('w.tasks.attach',[$team,$task]) }}" enctype="multipart/form-data" class="mt-4 space-y-2">@csrf<input type="file" name="file" required class="block w-full text-sm"><p class="text-xs text-slate-400">Images, PDF, Office, CSV, ZIP · max 5 MB</p><button class="btn-secondary w-full">Upload</button></form>@endif
  </div>
</div>
@endsection
