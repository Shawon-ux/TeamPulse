<?php
use App\Http\Controllers\{ActivityController,AuthController,BillingController,ChatController,ExportController,MemberController,NotificationController,ProfileController,ProjectController,TaskController,WorkspaceController};
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? app(WorkspaceController::class)->home(request()) : view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:20,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendReset'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');
    Route::get('/two-factor-challenge', [AuthController::class, 'challenge'])->name('2fa.challenge');
    Route::post('/two-factor-challenge', [AuthController::class, 'verifyChallenge'])->middleware('throttle:10,1');
});

Route::get('/invitations/{token}', [MemberController::class, 'accept'])->middleware('signed')->name('invitations.accept');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/avatar/{user}', [ProfileController::class, 'avatar'])->name('avatar');

    Route::get('/workspaces', [WorkspaceController::class, 'index'])->name('workspaces.index');
    Route::get('/workspaces/create', [WorkspaceController::class, 'create'])->name('workspaces.create');
    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::post('/profile/2fa', [ProfileController::class, 'enable2fa'])->name('2fa.enable');
    Route::post('/profile/2fa/confirm', [ProfileController::class, 'confirm2fa'])->name('2fa.confirm');
    Route::delete('/profile/2fa', [ProfileController::class, 'disable2fa'])->name('2fa.disable');
    Route::post('/profile/sessions/logout-others', [ProfileController::class, 'logoutOthers'])->name('sessions.others');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::get('/notifications/all', [NotificationController::class, 'page'])->name('notifications.all');
    Route::post('/notifications/read/{id?}', [NotificationController::class, 'read'])->name('notifications.read');

    /* Tenant-scoped routes */
    Route::prefix('workspaces/{team:slug}')->middleware('tenant')->name('w.')->group(function () {
        Route::get('/', [WorkspaceController::class, 'dashboard'])->name('dashboard');
        Route::get('/notifications', [NotificationController::class, 'page'])->name('notifications');

        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::get('/attachments/{attachment}', [TaskController::class, 'download'])->name('attachments.download');

        Route::get('/chat/{channel?}', [ChatController::class, 'index'])->name('chat');
        Route::get('/chat/{channel}/messages', [ChatController::class, 'fetch'])->name('chat.fetch');

        Route::get('/members', [MemberController::class, 'index'])->name('members.index');
        Route::post('/leave', [MemberController::class, 'leave'])->name('leave');
        Route::get('/billing', [BillingController::class, 'index'])->name('billing')->middleware('can.team:manage');

        Route::middleware('can.team:write')->group(function () {
            Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
            Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy')->middleware('can.team:manage');
            Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');
            Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
            Route::post('/tasks/{task}/move', [TaskController::class, 'move'])->name('tasks.move');
            Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
            Route::post('/tasks/{task}/comments', [TaskController::class, 'comment'])->name('tasks.comment');
            Route::post('/tasks/{task}/attachments', [TaskController::class, 'attach'])->name('tasks.attach');
            Route::post('/chat/{channel}/messages', [ChatController::class, 'send'])->name('chat.send');
            Route::post('/chat/{channel}/typing', [ChatController::class, 'typing'])->name('chat.typing');
        });

        Route::middleware('can.team:manage')->group(function () {
            Route::post('/chat-channels', [ChatController::class, 'createChannel'])->name('chat.channels');
            Route::post('/members/invite', [MemberController::class, 'invite'])->name('members.invite');
            Route::delete('/invitations/{invitation}', [MemberController::class, 'revoke'])->name('invitations.revoke');
            Route::put('/members/{user}', [MemberController::class, 'updateRole'])->name('members.role');
            Route::delete('/members/{user}', [MemberController::class, 'remove'])->name('members.remove');
            Route::get('/settings', [WorkspaceController::class, 'settings'])->name('settings');
            Route::put('/settings', [WorkspaceController::class, 'update'])->name('settings.update');
            Route::get('/activity', [ActivityController::class, 'index'])->name('activity')->middleware('plan:audit');
            Route::post('/exports', [ExportController::class, 'queue'])->name('exports.queue')->middleware('plan:exports');
            Route::get('/exports/{file}', [ExportController::class, 'download'])->name('exports.download');
        });

        Route::middleware('can.team:billing')->group(function () {
            Route::post('/billing', [BillingController::class, 'change'])->name('billing.change');
            Route::delete('/settings', [WorkspaceController::class, 'destroy'])->name('settings.destroy');
        });
    });

    /* Super admin */
    Route::prefix('admin')->middleware('super')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users/{user}/suspend', [AdminController::class, 'toggleSuspend'])->name('users.suspend');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::get('/workspaces', [AdminController::class, 'teams'])->name('teams');
        Route::put('/workspaces/{team}/plan', [AdminController::class, 'setPlan'])->name('teams.plan');
        Route::delete('/workspaces/{team}', [AdminController::class, 'destroyTeam'])->name('teams.destroy');
        Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
    });
});
