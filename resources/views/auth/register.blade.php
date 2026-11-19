@extends('layouts.guest')
@section('title', 'Create account · TeamPulse')
@section('content')
<h2 class="text-3xl font-extrabold">Create your account</h2>
<p class="mt-1 text-slate-500">Start with a free workspace. No credit card needed.</p>
<form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">@csrf
  <div><label class="label">Full name</label><input name="name" value="{{ old('name') }}" required autofocus class="input">@error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
  <div><label class="label">Email</label><input type="email" name="email" value="{{ old('email', $email) }}" required class="input" {{ $email ? 'readonly' : '' }}>@error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
  @unless(session('invite_token'))
  <div><label class="label">Workspace name <span class="text-slate-400">(optional)</span></label><input name="workspace" value="{{ old('workspace') }}" placeholder="Acme Inc." class="input"></div>
  @endunless
  <div class="grid gap-4 sm:grid-cols-2">
    <div><label class="label">Password</label><input type="password" name="password" required class="input">@error('password')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
    <div><label class="label">Confirm</label><input type="password" name="password_confirmation" required class="input"></div>
  </div>
  <p class="text-xs text-slate-500">Minimum 8 characters.</p>
  <button class="btn-primary w-full py-2.5">Create account</button>
</form>
<p class="mt-6 text-center text-sm text-slate-500">Already have an account? <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:underline">Sign in</a></p>
@endsection
