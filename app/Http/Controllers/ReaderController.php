<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;

class ReaderController extends Controller
{
    public function show(Request $request, Story $story, int $chapter)
    {
        $chapterModel = $story->chapters()->where('number', $chapter)->firstOrFail();
        $previous = $story->chapters()->where('number', '<', $chapter)->reorder('number', 'desc')->first();
        $next = $story->chapters()->where('number', '>', $chapter)->orderBy('number')->first();
        $chapters = $story->chapters()->select(['id', 'number', 'title'])->get();

        $progress = $request->user()
            ? $request->user()->readingProgress()->where('story_id', $story->id)->first()
            : null;

        $bookmarked = $request->user()
            ? $request->user()->bookmarks()->where('chapter_id', $chapterModel->id)->exists()
            : false;

        return view('reader.show', [
            'story' => $story,
            'chapter' => $chapterModel,
            'chapters' => $chapters,
            'previous' => $previous,
            'next' => $next,
            'progress' => $progress,
            'bookmarked' => $bookmarked,
            'settings' => $this->settings($request),
        ]);
    }

    private function settings(Request $request): array
    {
        $user = $request->user();

        return [
            'theme' => $user?->theme_preference ?? 'dark',
            'fontSize' => $user?->reader_font_size ?? 18,
            'fontFamily' => $user?->reader_font_family ?? 'serif',
            'lineHeight' => $user?->reader_line_height ?? 1.75,
            'contentWidth' => $user?->reader_content_width ?? 760,
        ];
    }
}
