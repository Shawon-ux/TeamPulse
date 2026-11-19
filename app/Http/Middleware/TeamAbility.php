<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TeamAbility
{
    public function handle(Request $request, Closure $next, string $ability)
    {
        abort_unless($request->user()->canIn($request->route('team'), $ability), 403, 'Your role does not allow this action.');
        return $next($request);
    }
}
