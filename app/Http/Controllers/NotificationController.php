<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        return response()->json([
            'unread' => $u->unreadNotifications()->count(),
            'items' => $u->notifications()->latest()->limit(10)->get()->map(fn ($n) => [
                'id' => $n->id, 'title' => $n->data['title'] ?? '', 'body' => $n->data['body'] ?? '', 'url' => $n->data['url'] ?? '#',
                'read' => (bool) $n->read_at, 'time' => $n->created_at->diffForHumans()]),
        ]);
    }
    public function page(Request $r, ?\App\Models\Team $team = null)
    {
        $u = $r->user();
        $filter = $r->query('filter') === 'unread' ? 'unread' : 'all';
        $q = $filter === 'unread' ? $u->unreadNotifications() : $u->notifications();
        return view('notifications', ['items' => $q->latest()->paginate(20)->withQueryString(), 'filter' => $filter, 'unread' => $u->unreadNotifications()->count()]);
    }
    public function read(Request $r, string $id = null)
    {
        $q = $r->user()->unreadNotifications();
        if ($id) $q->where('id', $id);
        $q->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }
}
