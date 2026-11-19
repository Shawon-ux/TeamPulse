<?php
namespace Database\Seeders;

use App\Models\{ActivityLog,Channel,Invoice,Message,Project,Task,TaskComment,Team,User};
use App\Support\Members;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['name' => 'Super Admin', 'email' => 'admin@teampulse.test', 'password' => 'Admin@12345', 'is_super_admin' => true, 'email_verified_at' => now()]);

        $demo = User::create(['name' => 'Demo Owner', 'email' => 'demo@teampulse.test', 'password' => 'Demo@12345', 'email_verified_at' => now()]);
        $sarah = User::create(['name' => 'Sarah Chen', 'email' => 'sarah@teampulse.test', 'password' => 'Demo@12345', 'email_verified_at' => now()]);
        $mike = User::create(['name' => 'Mike Ross', 'email' => 'mike@teampulse.test', 'password' => 'Demo@12345', 'email_verified_at' => now()]);
        $guest = User::create(['name' => 'Gina Guest', 'email' => 'guest@teampulse.test', 'password' => 'Demo@12345', 'email_verified_at' => now()]);

        $team = Members::createWorkspace($demo, 'Acme Corp');
        $team->update(['plan' => 'pro', 'stripe_status' => 'active', 'plan_renews_at' => now()->addMonth()]);
        $team->members()->attach($sarah->id, ['role' => 'admin']);
        $team->members()->attach($mike->id, ['role' => 'member']);
        $team->members()->attach($guest->id, ['role' => 'guest']);
        Invoice::create(['team_id' => $team->id, 'number' => 'INV-DEMO0001', 'plan' => 'pro', 'amount' => 2900, 'status' => 'paid']);
        $sarah->forceFill(['current_team_id' => $team->id])->save();

        $site = $team->projects()->create(['name' => 'Website Redesign', 'description' => 'Refresh the marketing site and onboarding flow.', 'color' => 'indigo']);
        $app = $team->projects()->create(['name' => 'Mobile App v2', 'description' => 'Ship the next major mobile release.', 'color' => 'emerald']);
        $rows = [
            [$site, 'Design new hero section', 'done', 'high', $sarah, -3],
            [$site, 'Write landing page copy', 'review', 'medium', $mike, 1],
            [$site, 'Implement pricing page', 'in_progress', 'high', $demo, 3],
            [$site, 'Set up analytics', 'todo', 'low', $mike, 7],
            [$site, 'Fix mobile navigation bug', 'todo', 'urgent', $sarah, -1],
            [$app, 'Push notification service', 'in_progress', 'urgent', $mike, 2],
            [$app, 'Dark mode support', 'todo', 'medium', $sarah, 10],
            [$app, 'App Store screenshots', 'todo', 'low', $demo, 14],
            [$app, 'Crash reporting integration', 'done', 'medium', $demo, -5],
        ];
        foreach ($rows as $i => [$p, $title, $st, $pr, $u, $days]) {
            $t = $p->tasks()->create(['title' => $title, 'status' => $st, 'priority' => $pr, 'assigned_to' => $u->id, 'created_by' => $demo->id,
                'due_date' => now()->addDays($days), 'position' => $i, 'description' => 'Details to be refined with the team.']);
        }
        $first = Task::first();
        TaskComment::create(['task_id' => $first->id, 'user_id' => $sarah->id, 'body' => 'Mockups are ready for review, @Demo!']);

        foreach ([[$sarah, 'Morning team! Standup in 10 minutes.'], [$mike, 'Pushed the notification service branch 🚀'],
                  [$demo, 'Great work @Sarah, @Mike — pricing page is next.']] as [$u, $b]) {
            Message::create(['team_id' => $team->id, 'user_id' => $u->id, 'channel' => 'general', 'body' => $b]);
        }
        Message::create(['team_id' => $team->id, 'user_id' => $mike->id, 'channel' => 'random', 'body' => 'Anyone up for lunch?']);

        foreach (['project.created' => ['project' => 'Website Redesign'], 'member.invited' => ['email' => 'mike@teampulse.test'], 'billing.plan_changed' => ['from' => 'free', 'to' => 'pro']] as $a => $p) {
            ActivityLog::create(['team_id' => $team->id, 'user_id' => $demo->id, 'action' => $a, 'properties' => $p, 'ip_address' => '127.0.0.1']);
        }
        $demo->notify(new \App\Notifications\AppNotification('Welcome to TeamPulse', 'Explore projects, chat and billing.', route('w.dashboard', $team), 'bell'));
    }
}
