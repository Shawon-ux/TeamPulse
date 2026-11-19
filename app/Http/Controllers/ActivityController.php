<?php
namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $r, Team $team)
    {
        $logs = $team->activityLogs()->with('user')
            ->when($r->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('action', 'like', "%$s%")->orWhere('properties', 'like', "%$s%")
                ->orWhere('ip_address', 'like', "%$s%")->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%$s%"))))
            ->latest()->paginate(25)->withQueryString();
        return view('activity', compact('logs'));
    }
}
