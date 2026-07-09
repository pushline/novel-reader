<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function store(Request $request, Chapter $chapter)
    {
        $request->user()->bookmarks()->updateOrCreate(
            ['chapter_id' => $chapter->id],
            ['note' => $request->string('note')->toString() ?: null]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'bookmarked' => true,
                'message' => 'Bookmark saved.',
            ]);
        }

        return back()->with('status', 'Bookmark saved.');
    }

    public function destroy(Request $request, Chapter $chapter)
    {
        $request->user()->bookmarks()->where('chapter_id', $chapter->id)->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'bookmarked' => false,
                'message' => 'Bookmark removed.',
            ]);
        }

        return back()->with('status', 'Bookmark removed.');
    }
}
