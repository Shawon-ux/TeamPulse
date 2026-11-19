<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'TeamPulse')</title>
  <link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
<div class="grid min-h-screen lg:grid-cols-2">
  <div class="relative hidden overflow-hidden bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-600 p-12 text-white lg:flex lg:flex-col lg:justify-between">
    <a href="{{ route('home') }}" class="flex items-center gap-2 text-2xl font-extrabold"><span class="grid h-9 w-9 place-items-center rounded-xl bg-white/20">⚡</span> TeamPulse</a>
    <div>
      <h2 class="text-4xl font-extrabold leading-tight">Where teams plan,<br>chat and ship — together.</h2>
      <ul class="mt-8 space-y-3 text-indigo-100">
        <li>✓ Isolated multi-tenant workspaces</li><li>✓ Kanban boards & real-time chat</li><li>✓ Role-based access & 2FA security</li><li>✓ Audit logs & subscription billing</li>
      </ul>
    </div>
    <p class="text-sm text-indigo-200">© {{ date('Y') }} TeamPulse</p>
    <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/10"></div><div class="absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-white/10"></div>
  </div>
  <div class="flex items-center justify-center p-6 sm:p-12">
    <div class="w-full max-w-md">
      <a href="{{ route('home') }}" class="mb-8 flex items-center gap-2 text-xl font-extrabold text-indigo-700 lg:hidden">⚡ TeamPulse</a>
      @include('partials.flash')
      @yield('content')
    </div>
  </div>
</div>
</body></html>
