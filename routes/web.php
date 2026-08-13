<?php

use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminContestController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminVideoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContestController;
use App\Http\Controllers\ContestEntryController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupPostController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| TalentStage - Routes (URL khop docs/SITEMAP.md)
|--------------------------------------------------------------------------
*/

/* ---------- Trang chung ---------- */
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::view('/sitemap', 'sitemap')->name('sitemap');

/* ---------- Xac thuc (FR1) ---------- */
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/* ---------- FR1: Ho so ---------- */
Route::get('/users/{user}', [ProfileController::class, 'show'])->whereNumber('user')->name('users.show');
Route::middleware('auth')->group(function () {
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

/* ---------- FR2: Video (them/sua/xoa cua Creator) ---------- */
Route::middleware('role:creator')->group(function () {
    Route::get('/videos/create', [VideoController::class, 'create'])->name('videos.create');
    Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
    Route::get('/my-videos', [VideoController::class, 'mine'])->name('videos.mine');
});
Route::middleware('auth')->group(function () {
    Route::get('/videos/{video}/edit', [VideoController::class, 'edit'])->whereNumber('video')->name('videos.edit');
    Route::put('/videos/{video}', [VideoController::class, 'update'])->whereNumber('video')->name('videos.update');
    Route::delete('/videos/{video}', [VideoController::class, 'destroy'])->whereNumber('video')->name('videos.destroy');
});
Route::get('/videos/{video}', [VideoController::class, 'show'])->whereNumber('video')->name('videos.show');

/* ---------- FR3: Kham pha ---------- */
Route::get('/explore', [ExploreController::class, 'index'])->name('explore');

/* ---------- FR4: Tuong tac ---------- */
Route::middleware('auth')->group(function () {
    Route::post('/videos/{video}/reaction', [ReactionController::class, 'store'])->whereNumber('video')->name('reactions.store');
    Route::post('/videos/{video}/comments', [CommentController::class, 'store'])->whereNumber('video')->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->whereNumber('comment')->name('comments.destroy');
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle'])->whereNumber('user')->name('follows.toggle');
    Route::get('/feed', [FeedController::class, 'index'])->name('feed');
});

/* ---------- FR5: Nhom & thao luan ---------- */
Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
Route::middleware('auth')->group(function () {
    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::post('/groups/{group}/join', [GroupController::class, 'join'])->whereNumber('group')->name('groups.join');
    Route::delete('/groups/{group}/leave', [GroupController::class, 'leave'])->whereNumber('group')->name('groups.leave');
    Route::post('/groups/{group}/posts', [GroupPostController::class, 'store'])->whereNumber('group')->name('groups.posts.store');
});
Route::get('/groups/{group}', [GroupController::class, 'show'])->whereNumber('group')->name('groups.show');

/* ---------- FR6: Nhan tin Creator <-> Mentor ---------- */
Route::middleware('role:creator,mentor')->group(function () {
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [MessageController::class, 'show'])->whereNumber('user')->name('messages.show');
    Route::post('/messages/{user}', [MessageController::class, 'store'])->whereNumber('user')->name('messages.store');
});

/* ---------- FR7: Cuoc thi ---------- */
Route::get('/contests', [ContestController::class, 'index'])->name('contests.index');
Route::get('/contests/{contest}', [ContestController::class, 'show'])->whereNumber('contest')->name('contests.show');
Route::middleware('auth')->group(function () {
    Route::post('/contests/{contest}/entries', [ContestEntryController::class, 'store'])->whereNumber('contest')->name('contests.entries.store');
    Route::post('/entries/{entry}/vote', [VoteController::class, 'store'])->whereNumber('entry')->name('entries.vote');
});

/* ---------- Thong bao ---------- */
Route::get('/notifications', [NotificationController::class, 'index'])->middleware('auth')->name('notifications.index');

/* ---------- Khu quan tri (FR8 + quan ly) ---------- */
Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/videos', [AdminVideoController::class, 'index'])->name('videos.index');
    Route::patch('/videos/{video}/approve', [AdminVideoController::class, 'approve'])->whereNumber('video')->name('videos.approve');
    Route::patch('/videos/{video}/reject', [AdminVideoController::class, 'reject'])->whereNumber('video')->name('videos.reject');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->whereNumber('user')->name('users.toggleActive');
    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::resource('contests', AdminContestController::class)->except(['show']);
});
