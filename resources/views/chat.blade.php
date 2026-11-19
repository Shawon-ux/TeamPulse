@extends('layouts.app')
@section('heading', $title)
@section('content')
@php $canWrite = auth()->user()->canIn($team,'write'); @endphp
<div class="card flex h-[calc(100vh-10rem)] overflow-hidden">
  <aside class="hidden w-56 shrink-0 overflow-y-auto border-r bg-slate-50 p-3 sm:block">
    <p class="px-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Channels</p>
    @foreach($channels as $c)<a href="{{ route('w.chat',[$team,$c->name]) }}" class="mt-1 block rounded-lg px-2 py-1.5 text-sm {{ $active===$c->name ? 'bg-indigo-600 text-white' : 'hover:bg-slate-200' }}"># {{ $c->name }}</a>@endforeach
    @if(in_array($role,['owner','admin']))<form method="POST" action="{{ route('w.chat.channels',$team) }}" class="mt-2 flex gap-1">@csrf<input name="name" placeholder="new-channel" class="input !py-1 text-xs"><button class="btn-secondary !px-2 !py-1">+</button></form>@endif
    <p class="mt-5 px-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Direct messages</p>
    @foreach($others as $o)@php $k = 'dm:'.min(auth()->id(),$o->id).':'.max(auth()->id(),$o->id); @endphp
    <a href="{{ route('w.chat',[$team,$k]) }}" class="mt-1 flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm {{ $active===$k ? 'bg-indigo-600 text-white' : 'hover:bg-slate-200' }}"><img src="{{ $o->avatarUrl() }}" class="h-5 w-5 rounded-full"> {{ $o->name }}</a>@endforeach
  </aside>
  <section class="flex min-w-0 flex-1 flex-col" x-data="chat({fetch:'{{ route('w.chat.fetch',[$team,$active]) }}', send:'{{ route('w.chat.send',[$team,$active]) }}', typing:'{{ route('w.chat.typing',[$team,$active]) }}'})">
    <div x-ref="box" class="flex-1 space-y-4 overflow-y-auto p-5">
      <template x-for="m in messages" :key="m.id">
        <div class="flex gap-3" :class="m.mine ? 'flex-row-reverse' : ''">
          <img :src="m.avatar" class="h-9 w-9 rounded-full">
          <div :class="m.mine ? 'items-end' : 'items-start'" class="flex max-w-[75%] flex-col">
            <p class="text-xs text-slate-400"><b class="text-slate-700" x-text="m.user"></b> · <span x-text="m.day + ' ' + m.at"></span></p>
            <p class="mt-0.5 whitespace-pre-wrap break-words rounded-2xl px-4 py-2 text-sm" :class="m.mine ? 'bg-indigo-600 text-white' : 'bg-slate-100'" x-text="m.body"></p>
          </div></div>
      </template>
      <p x-show="!messages.length" class="pt-16 text-center text-slate-400">No messages yet — say hello 👋</p>
    </div>
    <div class="h-5 px-5 text-xs italic text-slate-400"><span x-show="typing.length" x-text="typing.join(', ') + (typing.length>1 ? ' are' : ' is') + ' typing…'"></span></div>
    @if($canWrite)
    <form @submit.prevent="send()" class="flex gap-2 border-t p-3"><input x-model="body" @input="onType()" placeholder="Message {{ $title }} — use @@name to mention" class="input" autocomplete="off"><button class="btn-primary">Send</button></form>
    @else<p class="border-t p-3 text-center text-sm text-slate-400">Guests have read-only access.</p>@endif
  </section>
</div>
@endsection
