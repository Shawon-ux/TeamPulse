@extends('layouts.app')
@section('heading', 'Projects')
@section('content')
<div class="grid gap-6 lg:grid-cols-3">
  <div class="space-y-4 lg:col-span-2">
    @forelse($projects as $p)
    <a href="{{ route('w.projects.show', [$team,$p]) }}" class="card block p-5 hover:border-indigo-400">
      <div class="flex items-center justify-between"><div class="flex items-center gap-3"><span class="h-3 w-3 rounded-full bg-{{ $p->color }}-500"></span><h3 class="font-bold">{{ $p->name }}</h3></div><span class="badge bg-slate-100 text-slate-600">{{ $p->tasks_count }} tasks</span></div>
      <p class="mt-1 text-sm text-slate-500">{{ $p->description }}</p>
      @php $pct = $p->tasks_count ? round($p->done_count/$p->tasks_count*100) : 0; @endphp
      <div class="mt-4 h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-{{ $p->color }}-500" style="width:{{ $pct }}%"></div></div><p class="mt-1 text-xs text-slate-400">{{ $pct }}% complete</p>
    </a>
    @empty<div class="card p-12 text-center text-slate-500">No projects yet. Create your first one →</div>@endforelse
  </div>
  @if(auth()->user()->canIn($team,'write'))
  <form method="POST" action="{{ route('w.projects.store', $team) }}" class="card h-fit space-y-4 p-5">@csrf
    <h3 class="font-bold">New project</h3>
    <p class="text-xs text-slate-500">{{ $projects->count() }} / {{ $team->limit('projects') ?? '∞' }} projects on the {{ $team->planConfig()['name'] }} plan</p>
    <div><label class="label">Name</label><input name="name" required class="input"></div>
    <div><label class="label">Description</label><textarea name="description" rows="2" class="input"></textarea></div>
    <div><label class="label">Color</label><select name="color" class="input">@foreach(['indigo','emerald','rose','amber','sky','violet'] as $c)<option>{{ $c }}</option>@endforeach</select></div>
    <button class="btn-primary w-full">Create project</button>
  </form>@endif
</div>
@endsection
