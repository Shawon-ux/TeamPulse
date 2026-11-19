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
    public function read(Request $r, string $id = null)
    {
        $q = $r->user()->unreadNotifications();
        if ($id) $q->where('id', $id);
        $q->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }
}
