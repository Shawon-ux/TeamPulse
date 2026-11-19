@extends('layouts.app')
@section('heading', 'Platform audit logs')
@section('content')
<form class="mb-4 flex gap-2"><input name="q" value="{{ request('q') }}" placeholder="Search action, IP, details" class="input max-w-md"><button class="btn-secondary">Search</button></form>
<div class="card overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-50 text-left text-slate-500"><tr><th class="px-5 py-3">When</th><th>User</th><th>Workspace</th><th>Action</th><th>IP</th></tr></thead><tbody class="divide-y">
@foreach($logs as $l)<tr><td class="whitespace-nowrap px-5 py-3 text-slate-500">{{ $l->created_at->format('M j, H:i') }}</td><td>{{ $l->user?->name ?? '—' }}</td><td>{{ $l->team?->name ?? '—' }}</td><td><span class="badge bg-indigo-50 text-indigo-700">{{ $l->action }}</span></td><td class="font-mono text-xs">{{ $l->ip_address }}</td></tr>@endforeach
</tbody></table></div><div class="mt-4">{{ $logs->links() }}</div>
@endsection
