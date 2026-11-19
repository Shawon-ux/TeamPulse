@extends('layouts.app')
@section('heading', 'My profile')
@section('content')
<div class="grid max-w-5xl gap-6 lg:grid-cols-2">
 <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="card space-y-4 p-6">@csrf @method('PUT')<h3 class="font-bold">Profile</h3>
  <div class="flex items-center gap-4"><img src="{{ $u->avatarUrl() }}" class="h-16 w-16 rounded-full object-cover"><input type="file" name="avatar" accept="image/*" class="text-sm"></div>
  <div><label class="label">Name</label><input name="name" value="{{ $u->name }}" class="input"></div>
  <div><label class="label">Email</label><input type="email" name="email" value="{{ $u->email }}" class="input"></div><button class="btn-primary">Save</button></form>
 <form method="POST" action="{{ route('profile.password') }}" class="card space-y-4 p-6">@csrf @method('PUT')<h3 class="font-bold">Change password</h3>
  <div><label class="label">Current password</label><input type="password" name="current_password" class="input"></div>
  <div><label class="label">New password</label><input type="password" name="password" class="input"></div>
  <div><label class="label">Confirm</label><input type="password" name="password_confirmation" class="input"></div><button class="btn-primary">Update password</button></form>
 <div class="card space-y-4 p-6"><h3 class="font-bold">Two-factor authentication</h3>
  @if($u->hasTwoFactor())
   <p class="text-sm text-emerald-700">✓ Enabled — you'll be asked for an authenticator code at sign-in.</p>
   <form method="POST" action="{{ route('2fa.disable') }}" class="space-y-3">@csrf @method('DELETE')<input type="password" name="password" placeholder="Confirm password" class="input"><button class="btn-danger">Disable 2FA</button></form>
  @elseif($secret)
   <p class="text-sm text-slate-600">Scan with Google Authenticator / Authy, then enter the 6-digit code.</p>
   <div class="inline-block rounded-lg border p-2">{!! $qr !!}</div><p class="font-mono text-xs text-slate-500">Secret: {{ $secret }}</p>
   <form method="POST" action="{{ route('2fa.confirm') }}" class="flex gap-2">@csrf<input name="code" placeholder="123456" class="input"><button class="btn-primary">Confirm</button></form>
  @else
   <p class="text-sm text-slate-600">Add an extra layer of security using a TOTP authenticator app.</p>
   <form method="POST" action="{{ route('2fa.enable') }}">@csrf<button class="btn-primary">Set up 2FA</button></form>
  @endif</div>
 <div class="card p-6"><div class="flex items-center justify-between"><h3 class="font-bold">Browser sessions</h3>
   <form method="POST" action="{{ route('sessions.others') }}">@csrf<button class="text-sm text-rose-600 hover:underline">Sign out others</button></form></div>
  <ul class="mt-3 divide-y text-sm">@foreach($sessions as $s)<li class="py-2"><p class="font-mono">{{ $s->ip_address }} @if($s->id===session()->getId())<span class="badge bg-emerald-100 text-emerald-700">this device</span>@endif</p><p class="truncate text-xs text-slate-400">{{ $s->user_agent }} · {{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans() }}</p></li>@endforeach</ul></div>
</div>
@endsection
