@extends('layouts.app')
@section('heading', 'Admin overview')
@section('content')
@php $max = max(1, $series->max('c')); @endphp
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
 @foreach([['Users',$stats['users']],['Workspaces',$stats['teams']],['Projects',$stats['projects']],['Tasks',$stats['tasks']],['Messages',$stats['messages']],['MRR','$'.number_format($stats['mrr'])]] as [$l,$v])
 <div class="card p-5"><p class="text-sm text-slate-500">{{ $l }}</p><p class="mt-1 text-2xl font-extrabold">{{ $v }}</p></div>@endforeach
</div>
<div class="mt-6 grid gap-6 lg:grid-cols-3">
 <div class="card p-6 lg:col-span-2"><h3 class="font-bold">Sign-ups · last 14 days</h3>
  <div class="mt-6 flex h-40 items-end gap-2">@foreach($series as $s)<div class="flex flex-1 flex-col items-center gap-1"><div class="w-full rounded-t bg-indigo-500" style="height:{{ max(4,$s['c']/$max*100) }}%" title="{{ $s['d'] }}: {{ $s['c'] }}"></div><span class="text-[10px] text-slate-400">{{ \Illuminate\Support\Str::after($s['d'],' ') }}</span></div>@endforeach</div></div>
 <div class="card p-6"><h3 class="font-bold">Plans</h3>@foreach(['free','pro','enterprise'] as $p)<div class="mt-4 flex justify-between text-sm"><span class="capitalize">{{ $p }}</span><b>{{ $plans[$p] ?? 0 }}</b></div>@endforeach</div>
</div>
<div class="card mt-6"><h3 class="p-5 font-bold">Latest platform activity</h3><div class="divide-y text-sm">@foreach($recent as $a)<div class="flex justify-between px-5 py-3"><span><b>{{ $a->user?->name ?? 'System' }}</b> · {{ $a->action }} @if($a->team)<span class="text-slate-400">({{ $a->team->name }})</span>@endif</span><span class="text-slate-400">{{ $a->created_at->diffForHumans() }}</span></div>@endforeach</div></div>
@endsection
