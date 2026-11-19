<?php
namespace App\Http\Controllers;

use App\Models\{ActivityLog,User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash,Storage,DB};
use Illuminate\Validation\Rules\Password as PasswordRule;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\{ImageRenderer,RendererStyle\RendererStyle,Image\SvgImageBackEnd};
use BaconQrCode\Writer;

class ProfileController extends Controller
{
    public function edit(Request $r)
    {
        $u = $r->user();
        $qr = null; $secret = null;
        if (!$u->hasTwoFactor() && $u->two_factor_secret) {
            $secret = $u->two_factor_secret;
            $uri = (new Google2FA)->getQRCodeUrl('TeamPulse', $u->email, $secret);
            $qr = (new Writer(new ImageRenderer(new RendererStyle(180, 1), new SvgImageBackEnd)))->writeString($uri);
        }
        $sessions = DB::table('sessions')->where('user_id', $u->id)->orderByDesc('last_activity')->get();
        return view('profile', compact('u', 'qr', 'secret', 'sessions'));
    }

    public function update(Request $r)
    {
        $u = $r->user();
        $d = $r->validate(['name' => 'required|string|max:100', 'email' => 'required|email|unique:users,email,'.$u->id,
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048']);
        if ($r->hasFile('avatar')) {
            if ($u->avatar_path) Storage::disk('local')->delete($u->avatar_path);
            $d['avatar_path'] = $r->file('avatar')->store('avatars', 'local');
        }
        unset($d['avatar']);
        $u->update($d);
        return back()->with('success', 'Profile updated.');
    }

    public function password(Request $r)
    {
        $r->validate(['current_password' => 'required|current_password', 'password' => ['required', 'confirmed', PasswordRule::min(8)]]);
        $r->user()->update(['password' => $r->password]);
        ActivityLog::record('auth.password_changed', $r->user()->currentTeam);
        return back()->with('success', 'Password changed.');
    }

    public function avatar(User $user)
    {
        abort_unless($user->avatar_path && Storage::disk('local')->exists($user->avatar_path), 404);
        return response()->file(Storage::disk('local')->path($user->avatar_path));
    }

    public function enable2fa(Request $r)
    {
        $r->user()->forceFill(['two_factor_secret' => (new Google2FA)->generateSecretKey(), 'two_factor_confirmed_at' => null])->save();
        return back();
    }
    public function confirm2fa(Request $r)
    {
        $r->validate(['code' => 'required']);
        $u = $r->user();
        if (!(new Google2FA)->verifyKey($u->two_factor_secret, preg_replace('/\s+/', '', $r->code))) {
            return back()->withErrors(['code' => 'Invalid code — try again.']);
        }
        $u->forceFill(['two_factor_confirmed_at' => now()])->save();
        ActivityLog::record('auth.2fa_enabled', $u->currentTeam);
        return back()->with('success', 'Two-factor authentication enabled.');
    }
    public function disable2fa(Request $r)
    {
        $r->validate(['password' => 'required|current_password']);
        $r->user()->forceFill(['two_factor_secret' => null, 'two_factor_confirmed_at' => null])->save();
        ActivityLog::record('auth.2fa_disabled', $r->user()->currentTeam);
        return back()->with('success', 'Two-factor authentication disabled.');
    }
    public function logoutOthers(Request $r)
    {
        DB::table('sessions')->where('user_id', $r->user()->id)->where('id', '!=', $r->session()->getId())->delete();
        return back()->with('success', 'Signed out of other sessions.');
    }
}
