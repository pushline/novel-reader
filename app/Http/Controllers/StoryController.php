<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Story;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function index(Request $request)
    {
        $stories = Story::query()
            ->with(['authors', 'genres'])
            ->withCount('chapters')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search')->toString().'%';

                $query->where(fn ($query) => $query
                    ->where('title', 'like', $search)
                    ->orWhere('description', 'like', $search));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('genre'), function ($query) use ($request) {
                $query->whereHas('genres', fn ($query) => $query->where('slug', $request->string('genre')));
            })
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'count' => number_format($stories->total()),
                'html' => view('stories.partials.list', ['stories' => $stories])->render(),
            ]);
        }

        return view('stories.index', [
            'stories' => $stories,
            'genres' => Genre::query()->orderBy('name')->get(),
        ]);
    }

    public function show(Story $story)
    {
        $story->load(['authors', 'genres']);
        $firstChapter = $story->chapters()->first();
        $progress = request()->user()
            ? request()->user()->readingProgress()->where('story_id', $story->id)->with('chapter')->first()
            : null;

        return view('stories.show', [
            'story' => $story,
            'chapters' => $story->chapters()->paginate(50),
            'chapterCount' => $story->chapters()->count(),
            'firstChapter' => $firstChapter,
            'progress' => $progress,
            'totalWords' => $story->chapters()->sum('word_count'),
        ]);
    }
}
