@extends('layouts.app')
@section('heading', 'Dashboard')
@section('content')
@php $labels = \App\Models\Task::STATUSES; $total = max(1, $stats['tasks']); $colors=['todo'=>'bg-slate-400','in_progress'=>'bg-sky-500','review'=>'bg-amber-500','done'=>'bg-emerald-500']; @endphp
<div class="mb-6"><h2 class="text-2xl font-extrabold">Hi {{ explode(' ', auth()->user()->name)[0] }} 👋</h2><p class="text-slate-500">Here's what's happening in {{ $team->name }}.</p></div>
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
  @foreach ([['Projects',$stats['projects'],'folder','indigo'],['Members',$stats['members'],'users','emerald'],['Open tasks',$stats['tasks']-$stats['done'],'check','amber'],['Overdue',$overdue,'bell','rose']] as [$l,$v,$i,$c])
  <div class="card flex items-center gap-4 p-5"><div class="grid h-12 w-12 place-items-center rounded-xl bg-{{ $c }}-100 text-{{ $c }}-600"><x-icon :name="$i" class="h-6 w-6"/></div>
    <div><p class="text-sm text-slate-500">{{ $l }}</p><p class="text-2xl font-extrabold">{{ $v }}</p></div></div>
  @endforeach
</div>
<div class="mt-6 grid gap-6 lg:grid-cols-3">
  <div class="card p-6 lg:col-span-2">
    <h3 class="font-bold">Task progress</h3>
    <div class="mt-4 flex h-3 overflow-hidden rounded-full bg-slate-100">@foreach($labels as $k=>$l)<div class="{{ $colors[$k] }}" style="width: {{ round(($byStatus[$k] ?? 0)/$total*100) }}%"></div>@endforeach</div>
    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">@foreach($labels as $k=>$l)<div><span class="mr-1 inline-block h-2.5 w-2.5 rounded-full {{ $colors[$k] }}"></span><span class="text-sm text-slate-600">{{ $l }}</span><p class="text-xl font-bold">{{ $byStatus[$k] ?? 0 }}</p></div>@endforeach</div>
    <h3 class="mt-8 font-bold">My open tasks</h3>
    <div class="mt-3 divide-y">@forelse($mine as $t)
      <a href="{{ route('w.tasks.show', [$team,$t]) }}" class="flex items-center justify-between py-3 hover:bg-slate-50"><div><p class="font-medium">{{ $t->title }}</p><p class="text-xs text-slate-500">{{ $t->project->name }}</p></div>
      <span class="text-xs {{ $t->due_date && $t->due_date->isPast() ? 'text-rose-600 font-semibold' : 'text-slate-500' }}">{{ $t->due_date?->format('M j') ?? 'No date' }}</span></a>
    @empty<p class="py-6 text-center text-sm text-slate-400">Nothing assigned to you. 🎉</p>@endforelse</div>
  </div>
  <div class="card p-6"><h3 class="font-bold">Recent activity</h3>
    <ul class="mt-4 space-y-4">@forelse($activity as $a)<li class="flex gap-3 text-sm"><img src="{{ $a->user?->avatarUrl() }}" class="h-8 w-8 rounded-full"><div><p><b>{{ $a->user?->name ?? 'System' }}</b> {{ str_replace('.', ' ', $a->action) }}</p><p class="text-xs text-slate-400">{{ $a->created_at->diffForHumans() }}</p></div></li>
    @empty<li class="text-sm text-slate-400">No activity yet.</li>@endforelse</ul></div>
</div>
@endsection
