@extends('layouts.guest')
@section('content')
<h2 class="text-3xl font-extrabold">Create a workspace</h2><p class="mt-1 text-slate-500">A workspace is an isolated space for your team.</p>
<form method="POST" action="{{ route('workspaces.store') }}" class="mt-8 space-y-5">@csrf
  <div><label class="label">Workspace name</label><input name="name" required autofocus class="input" placeholder="Acme Inc.">@error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
  <button class="btn-primary w-full py-2.5">Create workspace</button></form>
<p class="mt-6 text-center text-sm"><a href="{{ route('home') }}" class="text-indigo-600 hover:underline">← Cancel</a></p>
@endsection
