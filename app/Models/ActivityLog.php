<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ActivityLog extends Model
{
    protected $fillable = ['team_id','user_id','action','properties','ip_address'];
    protected $casts = ['properties' => 'array'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function team(): BelongsTo { return $this->belongsTo(Team::class); }

    public static function record(string $action, ?Team $team = null, array $props = [], ?User $user = null): self
    {
        return static::create([
            'team_id' => $team?->id, 'user_id' => ($user ?? auth()->user())?->id,
            'action' => $action, 'properties' => $props, 'ip_address' => request()->ip(),
        ]);
    }
}
