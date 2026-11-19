<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo,BelongsToMany,HasMany};

class Team extends Model
{
    protected $fillable = ['user_id','name','slug','plan','stripe_id','stripe_status','plan_renews_at'];
    protected $casts = ['plan_renews_at' => 'datetime'];

    public function getRouteKeyName(): string { return 'slug'; }
    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function members(): BelongsToMany { return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps(); }
    public function projects(): HasMany { return $this->hasMany(Project::class); }
    public function invitations(): HasMany { return $this->hasMany(Invitation::class); }
    public function channels(): HasMany { return $this->hasMany(Channel::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
    public function activityLogs(): HasMany { return $this->hasMany(ActivityLog::class); }

    public function planConfig(): array { return config('plans.'.$this->plan, config('plans.free')); }
    public function limit(string $key) { return $this->planConfig()[$key] ?? null; }
    public function within(string $key, int $current): bool
    {
        $l = $this->limit($key);
        return $l === null || $current < $l;
    }
    public function hasFeature(string $feature): bool { return in_array($this->plan, ['pro','enterprise']); }

    public static function uniqueSlug(string $name): string
    {
        $base = \Illuminate\Support\Str::slug($name) ?: 'workspace';
        $slug = $base; $i = 1;
        while (static::where('slug', $slug)->exists()) { $slug = $base.'-'.(++$i); }
        return $slug;
    }
}
