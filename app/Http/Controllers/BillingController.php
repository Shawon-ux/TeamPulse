<?php
namespace App\Http\Controllers;

use App\Models\{ActivityLog,Invoice,Team};
use Illuminate\Http\Request;

/**
 * Billing runs in "simulation" mode unless STRIPE_KEY/STRIPE_SECRET are configured.
 * The plan / status columns mirror what Cashier's webhook handlers would update.
 */
class BillingController extends Controller
{
    public function index(Team $team)
    {
        return view('billing', [
            'plans' => config('plans'), 'invoices' => $team->invoices()->latest()->get(),
            'usage' => ['projects' => $team->projects()->count(), 'members' => $team->members()->count()],
            'stripeLive' => (bool) config('services.stripe.secret'),
        ]);
    }

    public function change(Request $r, Team $team)
    {
        $d = $r->validate(['plan' => 'required|in:free,pro,enterprise']);
        $new = $d['plan'];
        if ($new === $team->plan) return back();
        $cfg = config("plans.$new");
        if ($team->limit('projects') !== $cfg['projects'] && $cfg['projects'] !== null && $team->projects()->count() > $cfg['projects']) {
            return back()->with('error', 'You have more projects than the '.$cfg['name'].' plan allows. Remove some first.');
        }
        if ($cfg['members'] !== null && $team->members()->count() > $cfg['members']) {
            return back()->with('error', 'You have more members than the '.$cfg['name'].' plan allows.');
        }
        $old = $team->plan;
        $team->update(['plan' => $new, 'stripe_status' => $new === 'free' ? null : 'active',
            'plan_renews_at' => $new === 'free' ? null : now()->addMonth()]);
        if ($cfg['price'] > 0) {
            Invoice::create(['team_id' => $team->id, 'number' => 'INV-'.strtoupper(substr(md5(uniqid()), 0, 8)),
                'plan' => $new, 'amount' => $cfg['price'] * 100, 'status' => 'paid']);
        }
        ActivityLog::record('billing.plan_changed', $team, ['from' => $old, 'to' => $new]);
        $team->owner->notify(new \App\Notifications\AppNotification('Plan changed', "{$team->name} is now on the {$cfg['name']} plan.", route('w.billing', $team), 'credit-card'));
        return back()->with('success', "Plan changed to {$cfg['name']}.");
    }
}
