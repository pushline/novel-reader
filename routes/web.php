<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\ReadingProgressController;
use App\Http\Controllers\ReaderController;
use App\Http\Controllers\StoryController;
use App\Models\Bookmark;
use App\Models\Chapter;
use App\Models\ReadingProgress;
use App\Models\Story;
use Illuminate\Http\Request;

Route::get('/', [StoryController::class, 'index'])->name('home');
Route::get('/stories/{story:slug}', [StoryController::class, 'show'])->name('stories.show');
Route::get('/stories/{story:slug}/chapters/{chapter}', [ReaderController::class, 'show'])->name('chapters.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function (Request $request) {
        return view('dashboard', [
            'storyCount' => Story::query()->count(),
            'chapterCount' => Chapter::query()->count(),
            'bookmarkCount' => Bookmark::query()->whereBelongsTo($request->user())->count(),
            'progressItems' => ReadingProgress::query()
                ->whereBelongsTo($request->user())
                ->with(['story', 'chapter'])
                ->latest('last_read_at')
                ->take(5)
                ->get(),
            'freshStories' => Story::query()
                ->with(['genres'])
                ->withCount('chapters')
                ->latest('updated_at')
                ->take(4)
                ->get(),
        ]);
    })->name('dashboard');
    Route::get('library', LibraryController::class)->name('library');
    Route::post('chapters/{chapter}/bookmarks', [BookmarkController::class, 'store'])->name('bookmarks.store');
    Route::delete('chapters/{chapter}/bookmarks', [BookmarkController::class, 'destroy'])->name('bookmarks.destroy');
    Route::post('chapters/{chapter}/progress', [ReadingProgressController::class, 'store'])->name('progress.store');
});

require __DIR__.'/settings.php';
