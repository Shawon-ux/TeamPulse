@extends('layouts.app')
@section('heading', 'Members')
@section('content')
@php $mgr = in_array($role,['owner','admin']); $rc=['owner'=>'bg-violet-100 text-violet-700','admin'=>'bg-indigo-100 text-indigo-700','member'=>'bg-emerald-100 text-emerald-700','guest'=>'bg-slate-100 text-slate-600']; @endphp
<div class="grid gap-6 lg:grid-cols-3">
 <div class="space-y-6 lg:col-span-2">
  <div class="card divide-y">
   <div class="flex items-center justify-between p-4"><h3 class="font-bold">Team ({{ $members->count() }}{{ $team->limit('members') ? ' / '.$team->limit('members') : '' }})</h3>
     @if($role!=='owner')<form method="POST" action="{{ route('w.leave',$team) }}" onsubmit="return confirm('Leave this workspace?')">@csrf<button class="text-sm text-rose-600 hover:underline">Leave workspace</button></form>@endif</div>
   @foreach($members as $m)
   <div class="flex flex-wrap items-center justify-between gap-3 p-4">
     <div class="flex items-center gap-3"><img src="{{ $m->avatarUrl() }}" class="h-10 w-10 rounded-full"><div><p class="font-medium">{{ $m->name }} @if($m->id===auth()->id())<span class="text-xs text-slate-400">(you)</span>@endif</p><p class="text-sm text-slate-500">{{ $m->email }}</p></div></div>
     <div class="flex items-center gap-2">
       @if($mgr && $m->pivot->role!=='owner' && ($m->pivot->role!=='admin' || $role==='owner'))
        <form method="POST" action="{{ route('w.members.role',[$team,$m]) }}" class="flex gap-1">@csrf @method('PUT')<select name="role" onchange="this.form.submit()" class="input !py-1 text-sm">@foreach(['admin','member','guest'] as $r)<option @selected($m->pivot->role===$r)>{{ $r }}</option>@endforeach</select></form>
        <form method="POST" action="{{ route('w.members.remove',[$team,$m]) }}" onsubmit="return confirm('Remove {{ $m->name }}?')">@csrf @method('DELETE')<button class="rounded-lg px-2 py-1 text-sm text-rose-600 hover:bg-rose-50">Remove</button></form>
       @else<span class="badge {{ $rc[$m->pivot->role] }}">{{ $m->pivot->role }}</span>@endif
     </div></div>
   @endforeach
  </div>
  @if($mgr && $invitations->count())
  <div class="card divide-y"><h3 class="p-4 font-bold">Pending invitations</h3>
   @foreach($invitations as $i)<div class="flex items-center justify-between p-4 text-sm"><div><p class="font-medium">{{ $i->email }}</p><p class="text-slate-500">{{ $i->role }} · expires {{ $i->expires_at->diffForHumans() }}</p></div>
   <form method="POST" action="{{ route('w.invitations.revoke',[$team,$i]) }}">@csrf @method('DELETE')<button class="text-rose-600 hover:underline">Revoke</button></form></div>@endforeach</div>
  @endif
 </div>
 <div>
 @if($mgr)
  <form method="POST" action="{{ route('w.members.invite',$team) }}" class="card space-y-4 p-5">@csrf<h3 class="font-bold">Invite a teammate</h3>
   <div><label class="label">Email</label><input type="email" name="email" required class="input"></div>
   <div><label class="label">Role</label><select name="role" class="input"><option value="member">Member</option><option value="admin">Admin</option><option value="guest">Guest (read-only)</option></select></div>
   <button class="btn-primary w-full">Send invitation</button><p class="text-xs text-slate-400">Links are signed and expire in 7 days.</p></form>
  <div class="card mt-6 p-5 text-sm text-slate-600"><h3 class="mb-2 font-bold text-slate-800">Role permissions</h3>
   <p><b>Owner</b> — everything incl. billing & deletion</p><p><b>Admin</b> — members, settings, channels</p><p><b>Member</b> — projects, tasks, chat</p><p><b>Guest</b> — read-only</p></div>
 @endif
 </div>
</div>
@endsection
