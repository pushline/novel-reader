<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('library.index', [
            'bookmarks' => $request->user()
                ->bookmarks()
                ->with(['chapter.story'])
                ->latest()
                ->get(),
            'progressItems' => $request->user()
                ->readingProgress()
                ->with(['story', 'chapter'])
                ->latest('last_read_at')
                ->get(),
        ]);
    }
}
