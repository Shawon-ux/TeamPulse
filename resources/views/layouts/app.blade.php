<!DOCTYPE html>
<html lang="en">
<head>
  <script>try{if(localStorage.theme==='dark'||(!localStorage.theme&&matchMedia('(prefers-color-scheme: dark)').matches))document.documentElement.classList.add('dark')}catch(e){}</script><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') · TeamPulse</title>
  <link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased" x-data="{ side: false }">
@php
  $u = auth()->user();
  $isAdminArea = request()->routeIs('admin.*');
  $nav = [];
  if (isset($team) && !$isAdminArea) {
    $nav = [
      ['w.dashboard', 'Dashboard', 'home', true], ['w.projects.index', 'Projects', 'folder', true], ['w.chat', 'Team Chat', 'chat', true],
      ['w.members.index', 'Members', 'users', true], ['w.activity', 'Audit Log', 'log', $role && in_array($role, ['owner','admin'])],
      ['w.billing', 'Billing', 'card', $role && in_array($role, ['owner','admin'])], ['w.settings', 'Settings', 'cog', $role && in_array($role, ['owner','admin'])],
    ];
  }
  $adminNav = [['admin.dashboard','Overview','chart'],['admin.users','Users','users'],['admin.teams','Workspaces','building'],['admin.logs','Audit Logs','log']];
@endphp
<div class="flex min-h-screen">
  <div x-show="side" x-cloak @click="side=false" class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>
  <aside :class="side ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-slate-900 text-slate-300 transition-transform">
    <a href="{{ route('home') }}" class="flex h-16 items-center gap-2 px-5 text-xl font-extrabold text-white"><span class="grid h-8 w-8 place-items-center rounded-lg bg-indigo-500">⚡</span> TeamPulse</a>

    @if (isset($team) && !$isAdminArea)
    <div x-data="{o:false}" class="relative mx-3 mb-3">
      <button @click="o=!o" class="flex w-full items-center justify-between rounded-lg bg-slate-800 px-3 py-2 text-left text-sm hover:bg-slate-700">
        <span class="truncate"><span class="block truncate font-semibold text-white">{{ $team->name }}</span><span class="text-xs capitalize text-slate-400">{{ $role }} · {{ $team->planConfig()['name'] }}</span></span> <span>▾</span>
      </button>
      <div x-show="o" x-cloak @click.outside="o=false" class="absolute left-0 right-0 z-50 mt-1 rounded-lg bg-white py-1 text-sm text-slate-700 shadow-xl">
        @foreach ($u->teams as $t)
          <a href="{{ route('w.dashboard', $t) }}" class="flex items-center justify-between px-3 py-2 hover:bg-slate-100">{{ $t->name }} @if($t->id===$team->id)<span class="text-indigo-600">✓</span>@endif</a>
        @endforeach
        <a href="{{ route('workspaces.create') }}" class="block border-t px-3 py-2 text-indigo-600 hover:bg-slate-100">+ New workspace</a>
      </div>
    </div>
    @endif

    <nav class="flex-1 space-y-1 overflow-y-auto px-3">
      @if ($isAdminArea)
        <p class="px-3 pb-1 pt-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Administration</p>
        @foreach ($adminNav as [$r, $l, $i])
          <a href="{{ route($r) }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs($r) ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}"><x-icon :name="$i"/> {{ $l }}</a>
        @endforeach
      @else
        @foreach ($nav as [$r, $l, $i, $show])
          @if ($show)
          <a href="{{ route($r, $team) }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs($r.'*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}"><x-icon :name="$i"/> {{ $l }}</a>
          @endif
        @endforeach
      @endif
      @if ($u->is_super_admin)
        <div class="my-3 border-t border-slate-700"></div>
        @if ($isAdminArea)
          @if($u->teams->count()) <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-slate-800 hover:text-white"><x-icon name="home"/> Back to workspace</a>@endif
        @else
          <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-amber-300 hover:bg-slate-800"><x-icon name="shield"/> Admin panel</a>
        @endif
      @endif
    </nav>

    <div class="border-t border-slate-800 p-3">
      <a href="{{ route('profile') }}" class="flex items-center gap-3 rounded-lg p-2 hover:bg-slate-800">
        <img src="{{ $u->avatarUrl() }}" class="h-9 w-9 rounded-full object-cover" alt="">
        <span class="min-w-0"><span class="block truncate text-sm font-medium text-white">{{ $u->name }}</span><span class="block truncate text-xs text-slate-400">{{ $u->email }}</span></span>
      </a>
      <form method="POST" action="{{ route('logout') }}" class="mt-1">@csrf
        <button class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-slate-800 hover:text-white"><x-icon name="logout"/> Sign out</button>
      </form>
    </div>
  </aside>

  <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
    <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-8">
      <div class="flex items-center gap-3">
        <button @click="side=true" class="text-slate-600 lg:hidden"><x-icon name="menu" class="h-6 w-6"/></button>
        <h1 class="text-lg font-semibold">@yield('heading', 'Dashboard')</h1>
      </div>
      <div class="flex items-center gap-4">
        @yield('actions')
        <button x-data="{d:document.documentElement.classList.contains('dark')}" @click="d=!d;document.documentElement.classList.toggle('dark',d);try{localStorage.theme=d?'dark':'light'}catch(e){}" class="rounded-full p-2 text-slate-600 hover:bg-slate-100" title="Toggle night mode"><span x-text="d?'☀️':'🌙'" class="text-lg leading-none"></span></button>
        <div x-data="bell('{{ route('notifications') }}', '{{ route('notifications.read') }}')" class="relative">
          <button @click="open=!open" class="relative rounded-full p-2 text-slate-600 hover:bg-slate-100"><x-icon name="bell" class="h-6 w-6"/>
            <span x-show="unread>0" x-text="unread" x-cloak class="absolute -right-0.5 -top-0.5 grid h-5 min-w-[1.25rem] place-items-center rounded-full bg-rose-500 px-1 text-xs font-bold text-white"></span></button>
          <div x-show="open" x-cloak @click.outside="open=false" class="absolute right-0 mt-2 w-80 overflow-hidden rounded-xl border bg-white shadow-xl">
            <div class="flex items-center justify-between border-b px-4 py-3"><span class="font-semibold">Notifications</span><button @click="markAll()" class="text-xs text-indigo-600 hover:underline">Mark all read</button></div>
            <div class="max-h-96 divide-y overflow-y-auto">
              <template x-for="n in items" :key="n.id">
                <a :href="n.url" class="block px-4 py-3 text-sm hover:bg-slate-50" :class="n.read ? '' : 'bg-indigo-50/60'">
                  <p class="font-medium" x-text="n.title"></p><p class="text-slate-500" x-text="n.body"></p><p class="mt-1 text-xs text-slate-400" x-text="n.time"></p></a>
              </template>
              <p x-show="!items.length" class="px-4 py-8 text-center text-sm text-slate-400">You're all caught up 🎉</p>
            </div>
            <a href="{{ isset($team) ? route('w.notifications', $team) : route('notifications.all') }}" class="block border-t bg-slate-50 px-4 py-3 text-center text-sm font-semibold text-indigo-600 hover:bg-slate-100">See all notifications</a>
          </div>
        </div>
      </div>
    </header>
    <main class="flex-1 p-4 sm:p-8">
      @include('partials.flash')
      @yield('content')
    </main>
  </div>
</div>
</body></html>
