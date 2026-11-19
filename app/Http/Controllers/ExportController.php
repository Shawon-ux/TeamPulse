<?php
namespace App\Http\Controllers;

use App\Jobs\ExportTasksJob;
use App\Models\{ActivityLog,Team};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function queue(Request $r, Team $team)
    {
        ExportTasksJob::dispatch($team, $r->user());
        ActivityLog::record('export.requested', $team);
        return back()->with('success', 'Export queued — you will get a notification when it is ready.');
    }
    public function download(Team $team, string $file)
    {
        $path = "exports/{$team->id}/".basename($file);
        abort_unless(Storage::disk('local')->exists($path), 404);
        return Storage::disk('local')->download($path);
    }
}
