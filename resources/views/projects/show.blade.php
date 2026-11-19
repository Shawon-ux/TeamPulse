@extends('layouts.app')
@section('heading', $project->name)
@section('actions')
  @if($role && in_array($role,['owner','admin']))<form method="POST" action="{{ route('w.projects.destroy',[$team,$project]) }}" onsubmit="return confirm('Delete this project and all its tasks?')">@csrf @method('DELETE')<button class="text-sm text-rose-600 hover:underline">Delete project</button></form>@endif
@endsection
@section('content')
@php $canWrite = auth()->user()->canIn($team,'write'); $pc=['low'=>'bg-slate-100 text-slate-600','medium'=>'bg-sky-100 text-sky-700','high'=>'bg-amber-100 text-amber-700','urgent'=>'bg-rose-100 text-rose-700']; @endphp
<p class="mb-4 text-sm text-slate-500">{{ $project->description }} @unless($canWrite)<span class="badge ml-2 bg-amber-100 text-amber-700">Read-only (guest)</span>@endunless</p>
<div x-data="kanban('{{ route('w.tasks.move', [$team, '__ID__']) }}')" data-readonly="{{ $canWrite ? 0 : 1 }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
  @foreach(\App\Models\Task::STATUSES as $key => $label)
  <div class="rounded-xl bg-slate-100 p-3">
    <div class="mb-3 flex items-center justify-between px-1"><h3 class="text-sm font-bold uppercase tracking-wide text-slate-600">{{ $label }}</h3><span data-count class="rounded-full bg-white px-2 text-xs font-semibold">{{ count($tasks[$key] ?? []) }}</span></div>
    <div data-column="{{ $key }}" class="min-h-[4rem] space-y-3">
      @foreach($tasks[$key] ?? [] as $t)
      <div data-task="{{ $t->id }}" class="card cursor-grab p-3 active:cursor-grabbing">
        <a href="{{ route('w.tasks.show',[$team,$t]) }}" class="block text-sm font-semibold hover:text-indigo-600">{{ $t->title }}</a>
        <div class="mt-2 flex flex-wrap items-center gap-2"><span class="badge {{ $pc[$t->priority] }}">{{ $t->priority }}</span>
          @if($t->due_date)<span class="text-xs {{ $t->due_date->isPast() && $key!=='done' ? 'font-semibold text-rose-600' : 'text-slate-500' }}">📅 {{ $t->due_date->format('M j') }}</span>@endif
          @if($t->comments_count)<span class="text-xs text-slate-500">💬 {{ $t->comments_count }}</span>@endif
          @if($t->assignee)<img src="{{ $t->assignee->avatarUrl() }}" title="{{ $t->assignee->name }}" class="ml-auto h-6 w-6 rounded-full"> @endif</div>
      </div>
      @endforeach
    </div>
  </div>
  @endforeach
</div>
@if($canWrite)
<form method="POST" action="{{ route('w.tasks.store',[$team,$project]) }}" class="card mt-6 grid gap-4 p-5 md:grid-cols-6">@csrf
  <h3 class="font-bold md:col-span-6">Add a task</h3>
  <div class="md:col-span-2"><label class="label">Title</label><input name="title" required class="input"></div>
  <div><label class="label">Column</label><select name="status" class="input">@foreach(\App\Models\Task::STATUSES as $k=>$l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
  <div><label class="label">Priority</label><select name="priority" class="input">@foreach(['low','medium','high','urgent'] as $p)<option {{ $p=='medium'?'selected':'' }}>{{ $p }}</option>@endforeach</select></div>
  <div><label class="label">Due</label><input type="date" name="due_date" class="input"></div>
  <div><label class="label">Assignee</label><select name="assigned_to" class="input"><option value="">—</option>@foreach($members as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach</select></div>
  <div class="md:col-span-5"><input name="description" placeholder="Description (optional)" class="input"></div><button class="btn-primary">Add task</button>
</form>@endif
@endsection
