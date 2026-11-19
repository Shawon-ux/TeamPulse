@extends('layouts.app')
@section('heading', 'Workspaces')
@section('content')
<div class="card overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-50 text-left text-slate-500"><tr><th class="px-5 py-3">Workspace</th><th>Owner</th><th>Members</th><th>Projects</th><th>Plan</th><th></th></tr></thead><tbody class="divide-y">
@foreach($teams as $t)<tr><td class="px-5 py-3"><p class="font-medium">{{ $t->name }}</p><p class="font-mono text-xs text-slate-400">{{ $t->slug }}</p></td><td>{{ $t->owner->name }}</td><td>{{ $t->members_count }}</td><td>{{ $t->projects_count }}</td>
<td><form method="POST" action="{{ route('admin.teams.plan',$t) }}">@csrf @method('PUT')<select name="plan" onchange="this.form.submit()" class="input !w-32 !py-1 text-sm">@foreach(['free','pro','enterprise'] as $p)<option @selected($t->plan===$p)>{{ $p }}</option>@endforeach</select></form></td>
<td class="pr-5 text-right"><form method="POST" action="{{ route('admin.teams.destroy',$t) }}" onsubmit="return confirm('Delete workspace and all data?')">@csrf @method('DELETE')<button class="text-rose-600 hover:underline">Delete</button></form></td></tr>@endforeach
</tbody></table></div><div class="mt-4">{{ $teams->links() }}</div>
@endsection
