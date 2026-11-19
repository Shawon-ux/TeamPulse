@extends('layouts.app')
@section('heading', 'Notifications')
@section('actions')
<form method="POST" action="{{ route('notifications.read') }}" onsubmit="fetch(this.action,{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(()=>location.reload());return false"><button class="btn-secondary !py-1.5">Mark all as read</button></form>
@endsection
@section('content')
<div class="mx-auto max-w-4xl">
  <div class="mb-4 flex gap-2 text-sm">
    <a href="?filter=all" class="rounded-full px-4 py-1.5 {{ $filter==='all' ? 'bg-indigo-600 text-white' : 'bg-white border hover:bg-slate-50' }}">All</a>
    <a href="?filter=unread" class="rounded-full px-4 py-1.5 {{ $filter==='unread' ? 'bg-indigo-600 text-white' : 'bg-white border hover:bg-slate-50' }}">Unread ({{ $unread }})</a>
  </div>
  <div class="card divide-y">
    @forelse($items as $n)
    <a href="{{ $n->data['url'] ?? '#' }}" class="flex items-start gap-4 p-5 hover:bg-slate-50 {{ $n->read_at ? '' : 'bg-indigo-50/60' }}">
      <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-indigo-100 text-indigo-600"><x-icon :name="$n->data['icon'] ?? 'bell'"/></div>
      <div class="min-w-0 flex-1"><p class="font-semibold">{{ $n->data['title'] ?? '' }} @unless($n->read_at)<span class="ml-1 inline-block h-2 w-2 rounded-full bg-indigo-500"></span>@endunless</p>
        <p class="text-sm text-slate-600">{{ $n->data['body'] ?? '' }}</p></div>
      <span class="whitespace-nowrap text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
    </a>
    @empty<p class="py-16 text-center text-slate-400">No notifications here 🎉</p>@endforelse
  </div>
  <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
