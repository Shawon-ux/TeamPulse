@extends('layouts.guest')
@section('title', 'Sign in · TeamPulse')
@section('content')
<h2 class="text-3xl font-extrabold">Welcome back</h2>
<p class="mt-1 text-slate-500">Sign in to your TeamPulse account.</p>
<form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">@csrf
  <div><label class="label">Email</label><input type="email" name="email" value="{{ old('email') }}" required autofocus class="input">
    @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
  <div><div class="flex justify-between"><label class="label">Password</label><a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:underline">Forgot?</a></div>
    <input type="password" name="password" required class="input">@error('password')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
  <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600"> Remember me</label>
  <button class="btn-primary w-full py-2.5">Sign in</button>
</form>
<p class="mt-6 text-center text-sm text-slate-500">New to TeamPulse? <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:underline">Create an account</a></p>
@if (app()->environment('local'))
<div class="mt-8 rounded-lg border border-dashed border-slate-300 bg-white p-4 text-xs text-slate-600">
  <p class="mb-2 font-semibold text-slate-700">Demo accounts (local only)</p>
  <p>Admin: <code>admin@teampulse.test</code> / <code>Admin@12345</code></p>
  <p>Owner: <code>demo@teampulse.test</code> / <code>Demo@12345</code></p>
</div>@endif
@endsection
