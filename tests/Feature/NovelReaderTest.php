<?php

use App\Models\Bookmark;
use App\Models\Chapter;
use App\Models\Genre;
use App\Models\ReadingProgress;
use App\Models\Story;
use App\Models\User;

it('shows the story library with filters', function () {
    $genre = Genre::factory()->create(['name' => 'Mystery', 'slug' => 'mystery']);
    $story = Story::factory()->create(['title' => 'Overgeared', 'slug' => 'overgeared', 'status' => 'completed']);
    $story->genres()->attach($genre);
    Chapter::factory()->for($story)->create(['number' => 1, 'title' => 'A Door Opens']);

    $this->get('/?search=Over&genre=mystery&status=completed')
        ->assertOk()
        ->assertSee('Overgeared')
        ->assertSee('Mystery');
});

it('shows a story detail page with chapters', function () {
    $story = Story::factory()->create(['title' => 'Quiet Blade', 'slug' => 'quiet-blade']);
    Chapter::factory()->for($story)->create(['number' => 1, 'title' => 'First Cut']);

    $this->get(route('stories.show', $story))
        ->assertOk()
        ->assertSee('Quiet Blade')
        ->assertSee('First Cut');
});

it('orders story chapters by chapter number instead of import time', function () {
    $story = Story::factory()->create(['title' => 'Overgeared', 'slug' => 'overgeared']);
    Chapter::factory()->for($story)->create(['number' => 2059, 'title' => 'Chapter 2059']);
    Chapter::factory()->for($story)->create(['number' => 376, 'title' => 'Chapter 376']);

    $this->get(route('stories.show', $story))
        ->assertOk()
        ->assertSeeInOrder(['Chapter 376', 'Chapter 2059']);
});

it('shows the reader page with sanitized chapter html', function () {
    $story = Story::factory()->create(['slug' => 'quiet-blade']);
    $chapter = Chapter::factory()->for($story)->create([
        'number' => 1,
        'title' => 'First Cut',
        'content' => '<p>The opening paragraph.</p>',
    ]);

    $this->get(route('chapters.show', [$story, $chapter->number]))
        ->assertOk()
        ->assertSee('First Cut')
        ->assertSee('The opening paragraph.', false);
});

it('requires authentication for the personal library', function () {
    $this->get(route('library'))->assertRedirect(route('login'));
});

it('saves bookmarks and reading progress for authenticated users', function () {
    $user = User::factory()->create();
    $story = Story::factory()->create();
    $chapter = Chapter::factory()->for($story)->create(['number' => 7]);

    $this->actingAs($user)
        ->post(route('bookmarks.store', $chapter), ['note' => 'Return here'])
        ->assertRedirect();

    expect(Bookmark::whereBelongsTo($user)->whereBelongsTo($chapter)->exists())->toBeTrue();

    $this->actingAs($user)
        ->postJson(route('progress.store', $chapter), ['scroll_percent' => 64])
        ->assertOk()
        ->assertJson(['saved' => true]);

    expect(ReadingProgress::whereBelongsTo($user)->whereBelongsTo($story)->first())
        ->scroll_percent->toBe(64);
});

it('shows bookmarks and continue-reading items in the user library', function () {
    $user = User::factory()->create();
    $story = Story::factory()->create(['title' => 'Saved Story']);
    $chapter = Chapter::factory()->for($story)->create(['title' => 'Saved Chapter']);

    Bookmark::factory()->for($user)->for($chapter)->create();
    ReadingProgress::factory()->for($user)->for($story)->for($chapter)->create(['scroll_percent' => 25]);

    $this->actingAs($user)
        ->get(route('library'))
        ->assertOk()
        ->assertSee('Saved Story')
        ->assertSee('Saved Chapter')
        ->assertSee('25%');
});
