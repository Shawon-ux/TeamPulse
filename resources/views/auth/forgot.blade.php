@extends('layouts.guest')
@section('content')
<h2 class="text-3xl font-extrabold">Reset password</h2><p class="mt-1 text-slate-500">We'll email you a reset link.</p>
<form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">@csrf
  <div><label class="label">Email</label><input type="email" name="email" required class="input">@error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
  <button class="btn-primary w-full py-2.5">Send reset link</button></form>
<p class="mt-6 text-center text-sm"><a href="{{ route('login') }}" class="text-indigo-600 hover:underline">← Back to sign in</a></p>
@endsection
