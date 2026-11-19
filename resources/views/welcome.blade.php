<!DOCTYPE html>
<html lang="en"><head><script>try{if(localStorage.theme==='dark'||(!localStorage.theme&&matchMedia('(prefers-color-scheme: dark)').matches))document.documentElement.classList.add('dark')}catch(e){}</script><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>TeamPulse — Team workspace, boards, chat & billing</title>
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="bg-white font-sans text-slate-800 antialiased">
<header class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
  <span class="flex items-center gap-2 text-xl font-extrabold text-indigo-700"><span class="grid h-8 w-8 place-items-center rounded-lg bg-indigo-600 text-white">⚡</span> TeamPulse</span>
  <div class="flex items-center gap-3"><a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600">Sign in</a><a href="{{ route('register') }}" class="btn-primary">Get started free</a></div>
</header>
<section class="bg-gradient-to-b from-indigo-50 to-white">
  <div class="mx-auto max-w-4xl px-6 py-20 text-center">
    <span class="badge bg-indigo-100 text-indigo-700">Multi-tenant SaaS workspace</span>
    <h1 class="mt-5 text-5xl font-extrabold leading-tight tracking-tight sm:text-6xl">Plan, chat and ship <span class="bg-gradient-to-r from-indigo-600 to-fuchsia-600 bg-clip-text text-transparent">together</span></h1>
    <p class="mx-auto mt-5 max-w-2xl text-lg text-slate-600">Isolated workspaces, Kanban boards, live team chat, role-based access, audit trails and subscription billing — in one place.</p>
    <div class="mt-8 flex justify-center gap-3"><a href="{{ route('register') }}" class="btn-primary px-6 py-3 text-base">Create a workspace</a><a href="{{ route('login') }}" class="btn-secondary px-6 py-3 text-base">Sign in</a></div>
  </div>
</section>
<section class="mx-auto grid max-w-6xl gap-6 px-6 py-16 sm:grid-cols-2 lg:grid-cols-3">
  @foreach ([['🏢','Workspace isolation','Every organization lives in its own tenant boundary. Switch workspaces instantly.'],['🔐','RBAC + 2FA','Owner, Admin, Member and Guest roles, plus TOTP authenticator security.'],['📋','Kanban boards','Drag & drop tasks, priorities, due dates, comments and attachments.'],['💬','Team chat','Public channels and direct messages with live updates and typing indicators.'],['🔔','Notifications','Mentions, assignments and billing alerts in a live notification bell.'],['🧾','Billing & audit','Free, Pro and Enterprise tiers with plan gating and a searchable audit log.']] as [$i,$t,$d])
  <div class="card p-6"><div class="text-3xl">{{ $i }}</div><h3 class="mt-3 font-bold">{{ $t }}</h3><p class="mt-1 text-sm text-slate-600">{{ $d }}</p></div>
  @endforeach
</section>
<section class="mx-auto max-w-6xl px-6 pb-20">
  <h2 class="text-center text-3xl font-extrabold">Simple pricing</h2>
  <div class="mt-8 grid gap-6 md:grid-cols-3">
  @foreach (config('plans') as $k => $p)
    <div class="card p-6 {{ $k==='pro' ? 'ring-2 ring-indigo-600' : '' }}"><h3 class="font-bold">{{ $p['name'] }}</h3>
      <p class="mt-2 text-4xl font-extrabold">${{ $p['price'] }}<span class="text-base font-medium text-slate-500">/mo</span></p>
      <ul class="mt-4 space-y-2 text-sm text-slate-600">@foreach($p['features'] as $f)<li>✓ {{ $f }}</li>@endforeach</ul></div>
  @endforeach</div>
</section>
<footer class="border-t py-6 text-center text-sm text-slate-500">© {{ date('Y') }} TeamPulse</footer>
</body></html>
