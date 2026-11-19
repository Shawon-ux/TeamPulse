<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Feature gating by subscription tier. */
class RequirePlan
{
    public function handle(Request $request, Closure $next, string $feature)
    {
        $team = $request->route('team');
        if (!$team->hasFeature($feature)) {
            return redirect()->route('w.billing', $team)->with('error', ucfirst($feature).' requires the Pro plan or higher.');
        }
        return $next($request);
    }
}
