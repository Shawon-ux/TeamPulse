<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name','email','password','avatar_path','current_team_id','is_super_admin','is_suspended',
        'two_factor_secret','two_factor_confirmed_at','last_login_at','last_login_ip'];
    protected $hidden = ['password','remember_token','two_factor_secret'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', 'password' => 'hashed',
            'two_factor_secret' => 'encrypted', 'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime', 'is_super_admin' => 'boolean', 'is_suspended' => 'boolean',
        ];
    }

    const ABILITIES = [
        'owner'  => ['read','write','manage','billing','delete'],
        'admin'  => ['read','write','manage'],
        'member' => ['read','write'],
        'guest'  => ['read'],
    ];

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withPivot('role')->withTimestamps();
    }
    public function currentTeam(): BelongsTo { return $this->belongsTo(Team::class, 'current_team_id'); }

    public function roleIn(Team $team): ?string
    {
        return $this->teams()->where('teams.id', $team->id)->first()?->pivot->role;
    }
    public function canIn(Team $team, string $ability): bool
    {
        $role = $this->roleIn($team);
        return $role && in_array($ability, self::ABILITIES[$role] ?? []);
    }
    public function hasTwoFactor(): bool { return $this->two_factor_confirmed_at !== null; }
    public function avatarUrl(): string
    {
        return $this->avatar_path
            ? route('avatar', $this)
            : 'https://ui-avatars.com/api/?background=6366f1&color=fff&bold=true&name='.urlencode($this->name);
    }
}
