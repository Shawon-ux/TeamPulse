<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Attachment extends Model
{
    protected $fillable = ['team_id','task_id','user_id','original_name','path','mime','size'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
