<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TaskComment extends Model
{
    protected $fillable = ['task_id','user_id','body'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
