@foreach (['success' => 'emerald', 'error' => 'rose'] as $k => $c)
  @if (session($k))
    <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false, 6000)" class="mb-4 flex items-start justify-between rounded-lg border border-{{ $c }}-200 bg-{{ $c }}-50 px-4 py-3 text-sm text-{{ $c }}-800">
      <span>{{ session($k) }}</span><button @click="show=false" class="ml-4 text-{{ $c }}-600">✕</button>
    </div>
  @endif
@endforeach
@if (session('invite_url'))
  <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
    <b>Dev mode:</b> mail is written to the log. Share this invitation link manually:
    <input readonly onclick="this.select()" value="{{ session('invite_url') }}" class="input mt-2 font-mono text-xs">
  </div>
@endif
@if ($errors->any() && !request()->routeIs('login','register'))
  <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
    <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif
