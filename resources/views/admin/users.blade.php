@extends('layouts.app')
@section('heading', 'Users')
@section('content')
<form class="mb-4 flex gap-2"><input name="q" value="{{ request('q') }}" placeholder="Search name or email" class="input max-w-md"><button class="btn-secondary">Search</button></form>
<div class="card overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-50 text-left text-slate-500"><tr><th class="px-5 py-3">User</th><th>Workspaces</th><th>2FA</th><th>Last login</th><th>Status</th><th></th></tr></thead><tbody class="divide-y">
@foreach($users as $u)<tr><td class="px-5 py-3"><div class="flex items-center gap-3"><img src="{{ $u->avatarUrl() }}" class="h-8 w-8 rounded-full"><div><p class="font-medium">{{ $u->name }} @if($u->is_super_admin)<span class="badge bg-amber-100 text-amber-700">admin</span>@endif</p><p class="text-xs text-slate-500">{{ $u->email }}</p></div></div></td>
<td>{{ $u->teams_count }}</td><td>{{ $u->hasTwoFactor() ? '✓' : '—' }}</td><td class="text-slate-500">{{ $u->last_login_at?->diffForHumans() ?? 'never' }}</td>
<td>@if($u->is_suspended)<span class="badge bg-rose-100 text-rose-700">suspended</span>@else<span class="badge bg-emerald-100 text-emerald-700">active</span>@endif</td>
<td class="space-x-2 whitespace-nowrap pr-5 text-right">@unless($u->is_super_admin)
<form method="POST" action="{{ route('admin.users.suspend',$u) }}" class="inline">@csrf<button class="text-amber-600 hover:underline">{{ $u->is_suspended ? 'Restore' : 'Suspend' }}</button></form>
<form method="POST" action="{{ route('admin.users.destroy',$u) }}" class="inline" onsubmit="return confirm('Delete user?')">@csrf @method('DELETE')<button class="text-rose-600 hover:underline">Delete</button></form>@endunless</td></tr>@endforeach
</tbody></table></div><div class="mt-4">{{ $users->links() }}</div>
@endsection
