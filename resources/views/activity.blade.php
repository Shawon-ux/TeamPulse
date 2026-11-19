@extends('layouts.app')
@section('heading', 'Audit Log')
@section('actions')
<form method="POST" action="{{ route('w.exports.queue',$team) }}">@csrf<button class="btn-secondary !py-1.5"><x-icon name="download" class="h-4 w-4"/> Export tasks (CSV)</button></form>
@endsection
@section('content')
<form class="mb-4 flex gap-2"><input name="q" value="{{ request('q') }}" placeholder="Search action, user, IP, details…" class="input max-w-md"><button class="btn-secondary">Search</button></form>
<div class="card overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-50 text-left text-slate-500"><tr><th class="px-5 py-3">When</th><th>User</th><th>Action</th><th>Details</th><th>IP</th></tr></thead>
<tbody class="divide-y">@forelse($logs as $l)<tr><td class="whitespace-nowrap px-5 py-3 text-slate-500">{{ $l->created_at->format('M j, H:i') }}</td><td>{{ $l->user?->name ?? '—' }}</td><td><span class="badge bg-indigo-50 text-indigo-700">{{ $l->action }}</span></td>
<td class="max-w-xs truncate text-slate-500">{{ collect($l->properties)->map(fn($v,$k)=>"$k: ".(is_array($v)?json_encode($v):$v))->implode(', ') }}</td><td class="font-mono text-xs">{{ $l->ip_address }}</td></tr>
@empty<tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">No entries.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
