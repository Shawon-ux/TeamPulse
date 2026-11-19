<?php
namespace App\Http\Controllers;

use App\Models\{ActivityLog,User};
use App\Support\Members;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,Hash,Password};
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }
    public function showRegister(Request $r)
    {
        $email = null;
        if ($t = session('invite_token')) $email = \App\Models\Invitation::where('token', $t)->value('email');
        return view('auth.register', compact('email'));
    }

    public function register(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
            'workspace' => 'nullable|string|max:80',
        ]);
        $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']]);
        $joined = Members::acceptPending($user);
        if (!$joined) $joined = Members::createWorkspace($user, $data['workspace'] ?: $user->name."'s Workspace");
        Auth::login($user);
        $this->touchLogin($user, $r);
        $r->session()->regenerate();
        return redirect()->route('w.dashboard', $joined)->with('success', 'Welcome to TeamPulse!');
    }

    public function login(Request $r)
    {
        $cred = $r->validate(['email' => 'required|email', 'password' => 'required']);
        $key = Str::lower($r->email).'|'.$r->ip();
        if (\RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['email' => 'Too many attempts. Try again in '.\RateLimiter::availableIn($key).'s.']);
        }
        $user = User::where('email', $cred['email'])->first();
        if (!$user || !Hash::check($cred['password'], $user->password)) {
            \RateLimiter::hit($key, 60);
            ActivityLog::record('auth.failed_login', null, ['email' => $cred['email']], $user);
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }
        if ($user->is_suspended) return back()->withErrors(['email' => 'This account has been suspended.']);
        \RateLimiter::clear($key);

        if ($user->hasTwoFactor()) {
            $r->session()->put('2fa.user', $user->id);
            $r->session()->put('2fa.remember', $r->boolean('remember'));
            return redirect()->route('2fa.challenge');
        }
        return $this->finishLogin($r, $user, $r->boolean('remember'));
    }

    public function challenge() { abort_unless(session('2fa.user'), 403); return view('auth.two-factor-challenge'); }

    public function verifyChallenge(Request $r)
    {
        $user = User::findOrFail(session('2fa.user'));
        $r->validate(['code' => 'required|string']);
        $ok = (new Google2FA)->verifyKey($user->two_factor_secret, preg_replace('/\s+/', '', $r->code), 1);
        if (!$ok) return back()->withErrors(['code' => 'Invalid authentication code.']);
        $remember = session()->pull('2fa.remember', false);
        session()->forget('2fa.user');
        return $this->finishLogin($r, $user, $remember);
    }

    protected function finishLogin(Request $r, User $user, bool $remember)
    {
        Auth::login($user, $remember);
        $r->session()->regenerate();
        $this->touchLogin($user, $r);
        $joined = Members::acceptPending($user);
        if ($user->is_super_admin && !$joined) return redirect()->route('admin.dashboard');
        return redirect()->intended($joined ? route('w.dashboard', $joined) : route('home'));
    }

    protected function touchLogin(User $user, Request $r): void
    {
        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $r->ip()])->save();
        ActivityLog::record('auth.login', $user->currentTeam, [], $user);
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('home');
    }

    /* Password reset */
    public function showForgot() { return view('auth.forgot'); }
    public function sendReset(Request $r)
    {
        $r->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($r->only('email'));
        // Don't reveal whether the account exists.
        return back()->with('success', 'If that email exists, a reset link has been sent (see storage/logs/laravel.log in local dev).');
    }
    public function showReset(Request $r, string $token) { return view('auth.reset', ['token' => $token, 'email' => $r->email]); }
    public function reset(Request $r)
    {
        $r->validate(['token' => 'required', 'email' => 'required|email', 'password' => ['required', 'confirmed', PasswordRule::min(8)]]);
        $status = Password::reset($r->only('email', 'password', 'password_confirmation', 'token'), function ($user, $pw) {
            $user->forceFill(['password' => $pw, 'remember_token' => Str::random(60)])->save();
            ActivityLog::record('auth.password_reset', null, [], $user);
        });
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password reset. You can sign in now.')
            : back()->withErrors(['email' => __($status)]);
    }
}
