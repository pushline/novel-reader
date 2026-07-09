<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use Illuminate\Http\Request;

class ReadingProgressController extends Controller
{
    public function store(Request $request, Chapter $chapter)
    {
        $validated = $request->validate([
            'scroll_percent' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $request->user()->readingProgress()->updateOrCreate(
            ['story_id' => $chapter->story_id],
            [
                'chapter_id' => $chapter->id,
                'scroll_percent' => $validated['scroll_percent'],
                'last_read_at' => now(),
            ]
        );

        return response()->json(['saved' => true]);
    }
}
