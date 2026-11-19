<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo,HasMany};
class Project extends Model
{
    protected $fillable = ['team_id','name','description','status','color'];
    public function team(): BelongsTo { return $this->belongsTo(Team::class); }
    public function tasks(): HasMany { return $this->hasMany(Task::class)->orderBy('position'); }
}
