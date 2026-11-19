@extends('layouts.guest')
@section('content')
<h2 class="text-3xl font-extrabold">Choose a new password</h2>
<form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-4">@csrf
  <input type="hidden" name="token" value="{{ $token }}">
  <div><label class="label">Email</label><input type="email" name="email" value="{{ old('email', $email) }}" required class="input"></div>
  <div><label class="label">New password</label><input type="password" name="password" required class="input"></div>
  <div><label class="label">Confirm password</label><input type="password" name="password_confirmation" required class="input"></div>
  <button class="btn-primary w-full py-2.5">Reset password</button></form>
@endsection
