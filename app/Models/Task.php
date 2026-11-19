<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo,HasMany};
class Task extends Model
{
    const STATUSES = ['todo' => 'To Do', 'in_progress' => 'In Progress', 'review' => 'Review', 'done' => 'Done'];
    protected $fillable = ['project_id','assigned_to','created_by','title','description','status','priority','due_date','position'];
    protected $casts = ['due_date' => 'date'];
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function comments(): HasMany { return $this->hasMany(TaskComment::class)->with('user')->oldest(); }
    public function attachments(): HasMany { return $this->hasMany(Attachment::class); }
}
