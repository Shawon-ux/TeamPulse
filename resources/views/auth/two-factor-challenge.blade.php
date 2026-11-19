@extends('layouts.guest')
@section('content')
<h2 class="text-3xl font-extrabold">Two-factor check</h2><p class="mt-1 text-slate-500">Enter the 6-digit code from your authenticator app.</p>
<form method="POST" action="{{ url('/two-factor-challenge') }}" class="mt-8 space-y-5">@csrf
  <div><input name="code" inputmode="numeric" autocomplete="one-time-code" autofocus required maxlength="7" placeholder="123456" class="input text-center text-2xl tracking-[0.5em]">
  @error('code')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
  <button class="btn-primary w-full py-2.5">Verify</button></form>
<p class="mt-6 text-center text-sm"><a href="{{ route('login') }}" class="text-indigo-600 hover:underline">← Back to sign in</a></p>
@endsection
