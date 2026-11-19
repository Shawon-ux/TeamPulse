@extends('layouts.guest')
@section('content')
<h2 class="text-3xl font-extrabold">Your workspaces</h2>
<div class="mt-6 space-y-3">@foreach($teams as $t)<a href="{{ route('w.dashboard',$t) }}" class="card flex items-center justify-between p-4 hover:border-indigo-400"><div><p class="font-semibold">{{ $t->name }}</p><p class="text-sm text-slate-500 capitalize">{{ $t->pivot->role }} · {{ $t->members_count }} members</p></div><span>→</span></a>@endforeach</div>
<a href="{{ route('workspaces.create') }}" class="btn-primary mt-6 w-full">+ New workspace</a>
@endsection
