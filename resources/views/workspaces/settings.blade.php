@extends('layouts.app')
@section('heading', 'Workspace settings')
@section('content')
<div class="max-w-2xl space-y-6">
<form method="POST" action="{{ route('w.settings.update',$team) }}" class="card space-y-4 p-6">@csrf @method('PUT')<h3 class="font-bold">General</h3>
 <div><label class="label">Workspace name</label><input name="name" value="{{ $team->name }}" class="input"></div>
 <div><label class="label">URL</label><input readonly value="{{ url('/workspaces/'.$team->slug) }}" class="input bg-slate-50 text-slate-500"></div>
 <button class="btn-primary">Save</button></form>
@if($role==='owner')
<form method="POST" action="{{ route('w.settings.destroy',$team) }}" class="card space-y-4 border-rose-200 p-6" onsubmit="return confirm('This permanently deletes everything. Continue?')">@csrf @method('DELETE')
 <h3 class="font-bold text-rose-700">Danger zone</h3><p class="text-sm text-slate-600">Type <code class="rounded bg-slate-100 px-1">{{ $team->slug }}</code> to delete this workspace and all its data.</p>
 <input name="confirm" class="input" required><button class="btn-danger">Delete workspace</button></form>@endif
</div>
@endsection
