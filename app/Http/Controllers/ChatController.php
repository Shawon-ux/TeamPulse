<?php
namespace App\Http\Controllers;

use App\Models\{Channel,Message,Team,User};
use App\Notifications\AppNotification;
use App\Support\Mentions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    /** Resolve & authorize a channel key: "general" or "dm:{a}:{b}". */
    protected function channelKey(Request $r, Team $team, string $key): string
    {
        if (str_starts_with($key, 'dm:')) {
            $ids = array_map('intval', explode(':', $key)[1] ? array_slice(explode(':', $key), 1) : []);
            abort_unless(count($ids) === 2 && in_array($r->user()->id, $ids), 403);
            abort_unless($team->members()->whereIn('users.id', $ids)->count() === 2, 403);
            sort($ids);
            return 'dm:'.implode(':', $ids);
        }
        abort_unless($team->channels()->where('name', $key)->exists(), 404);
        return $key;
    }

    public function index(Request $r, Team $team, ?string $channel = 'general')
    {
        $key = $this->channelKey($r, $team, $channel);
        $others = $team->members()->where('users.id', '!=', $r->user()->id)->orderBy('name')->get();
        $title = '# '.$key;
        if (str_starts_with($key, 'dm:')) {
            $other = collect(explode(':', $key))->slice(1)->map(fn ($i) => (int) $i)->first(fn ($i) => $i !== $r->user()->id);
            $title = '@ '.(User::find($other)?->name ?? 'Direct message');
        }
        return view('chat', ['channels' => $team->channels()->orderBy('name')->get(), 'others' => $others, 'active' => $key, 'title' => $title]);
    }

    public function fetch(Request $r, Team $team, string $channel)
    {
        $key = $this->channelKey($r, $team, $channel);
        $after = (int) $r->query('after', 0);
        $q = Message::with('user:id,name,avatar_path')->where('team_id', $team->id)->where('channel', $key);
        $messages = $after ? $q->where('id', '>', $after)->orderBy('id')->get() : $q->latest('id')->limit(50)->get()->reverse()->values();
        $typing = collect(Cache::get("typing:{$team->id}:{$key}", []))->filter(fn ($t, $uid) => $uid != $r->user()->id && $t > now()->timestamp - 4)->keys();
        return response()->json([
            'messages' => $messages->map(fn ($m) => ['id' => $m->id, 'body' => $m->body, 'at' => $m->created_at->format('H:i'),
                'day' => $m->created_at->format('M j'), 'mine' => $m->user_id === $r->user()->id,
                'user' => $m->user->name, 'avatar' => $m->user->avatarUrl()])->values(),
            'typing' => User::whereIn('id', $typing)->pluck('name'),
        ]);
    }

    public function send(Request $r, Team $team, string $channel)
    {
        abort_unless($r->user()->canIn($team, 'write'), 403, 'Guests have read-only access.');
        $key = $this->channelKey($r, $team, $channel);
        $d = $r->validate(['body' => 'required|string|max:2000']);
        $m = Message::create(['team_id' => $team->id, 'user_id' => $r->user()->id, 'channel' => $key, 'body' => $d['body']]);
        foreach (Mentions::users($d['body'], $team) as $u) {
            if ($u->id !== $r->user()->id) $u->notify(new AppNotification('Mention in #'.$key, "{$r->user()->name}: ".str($d['body'])->limit(80), route('w.chat', [$team, $key]), 'at-sign'));
        }
        if (str_starts_with($key, 'dm:')) {
            foreach (array_slice(explode(':', $key), 1) as $id) if ((int) $id !== $r->user()->id) {
                User::find($id)?->notify(new AppNotification('New message from '.$r->user()->name, str($d['body'])->limit(80), route('w.chat', [$team, $key]), 'message'));
            }
        }
        $this->typing($r, $team, $channel, false);
        return response()->json(['id' => $m->id]);
    }

    public function typing(Request $r, Team $team, string $channel, bool $on = true)
    {
        $key = $this->channelKey($r, $team, $channel);
        $ck = "typing:{$team->id}:{$key}";
        $t = Cache::get($ck, []);
        if ($on) $t[$r->user()->id] = now()->timestamp; else unset($t[$r->user()->id]);
        Cache::put($ck, $t, 30);
        return response()->noContent();
    }

    public function createChannel(Request $r, Team $team)
    {
        abort_unless($r->user()->canIn($team, 'manage'), 403);
        $d = $r->validate(['name' => ['required', 'max:30', 'regex:/^[a-z0-9\-]+$/']]);
        $c = Channel::firstOrCreate(['team_id' => $team->id, 'name' => $d['name']]);
        return redirect()->route('w.chat', [$team, $c->name]);
    }
}
