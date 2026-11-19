<?php
namespace App\Jobs;

use App\Models\{Task,Team,User};
use App\Notifications\AppNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;

class ExportTasksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;
    public function __construct(public Team $team, public User $user) {}

    public function handle(): void
    {
        $rows = Task::with(['project','assignee'])->whereHas('project', fn ($q) => $q->where('team_id', $this->team->id))->get();
        $out = fopen('php://temp', 'r+');
        fputcsv($out, ['ID','Project','Title','Status','Priority','Assignee','Due date','Created']);
        foreach ($rows as $t) {
            fputcsv($out, [$t->id,$t->project->name,$t->title,$t->status,$t->priority,$t->assignee?->name,$t->due_date?->toDateString(),$t->created_at]);
        }
        rewind($out);
        $name = "exports/{$this->team->id}/tasks-".now()->format('Ymd-His').'.csv';
        Storage::disk('local')->put($name, stream_get_contents($out));
        $this->user->notify(new AppNotification('Export ready', 'Your tasks export is ready to download.',
            route('w.exports.download', [$this->team, basename($name)]), 'download'));
    }
}
