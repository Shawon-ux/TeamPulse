@extends('layouts.app')
@section('heading', 'Billing')
@section('content')
@if(!$stripeLive)<div class="mb-6 rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800"><b>Simulation mode:</b> no Stripe keys are configured, so plan changes apply instantly and generate demo invoices. Set <code>STRIPE_KEY</code> / <code>STRIPE_SECRET</code> and wire Cashier to charge for real.</div>@endif
<div class="grid gap-6 md:grid-cols-3">
 @foreach($plans as $k=>$p)
 <div class="card flex flex-col p-6 {{ $team->plan===$k ? 'ring-2 ring-indigo-600' : '' }}">
  <div class="flex items-center justify-between"><h3 class="text-lg font-bold">{{ $p['name'] }}</h3>@if($team->plan===$k)<span class="badge bg-indigo-100 text-indigo-700">Current</span>@endif</div>
  <p class="mt-2 text-4xl font-extrabold">${{ $p['price'] }}<span class="text-base font-medium text-slate-500">/mo</span></p>
  <ul class="my-5 flex-1 space-y-2 text-sm text-slate-600">@foreach($p['features'] as $f)<li>✓ {{ $f }}</li>@endforeach</ul>
  @if($role==='owner' && $team->plan!==$k)<form method="POST" action="{{ route('w.billing.change',$team) }}">@csrf<input type="hidden" name="plan" value="{{ $k }}"><button class="{{ $p['price'] > $plans[$team->plan]['price'] ? 'btn-primary' : 'btn-secondary' }} w-full">{{ $p['price'] > $plans[$team->plan]['price'] ? 'Upgrade' : 'Switch' }} to {{ $p['name'] }}</button></form>@endif
 </div>
 @endforeach
</div>
<div class="mt-6 grid gap-6 lg:grid-cols-3">
 <div class="card p-6"><h3 class="font-bold">Usage</h3>
  @foreach([['Projects',$usage['projects'],$team->limit('projects')],['Members',$usage['members'],$team->limit('members')]] as [$l,$v,$max])
  <div class="mt-4"><div class="flex justify-between text-sm"><span>{{ $l }}</span><span class="text-slate-500">{{ $v }} / {{ $max ?? '∞' }}</span></div><div class="mt-1 h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-indigo-500" style="width:{{ $max ? min(100,$v/$max*100) : 5 }}%"></div></div></div>@endforeach
  @if($team->plan_renews_at)<p class="mt-5 text-sm text-slate-500">Renews {{ $team->plan_renews_at->toFormattedDateString() }}</p>@endif
 </div>
 <div class="card lg:col-span-2"><h3 class="p-5 font-bold">Invoices</h3>
  <table class="w-full text-sm"><thead class="bg-slate-50 text-left text-slate-500"><tr><th class="px-5 py-2">Number</th><th>Plan</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
  <tbody class="divide-y">@forelse($invoices as $i)<tr><td class="px-5 py-3 font-mono">{{ $i->number }}</td><td class="capitalize">{{ $i->plan }}</td><td>${{ number_format($i->amount/100,2) }}</td><td><span class="badge bg-emerald-100 text-emerald-700">{{ $i->status }}</span></td><td>{{ $i->created_at->toFormattedDateString() }}</td></tr>
  @empty<tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No invoices yet.</td></tr>@endforelse</tbody></table></div>
</div>
@endsection
