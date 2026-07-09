<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Genre;
use App\Models\Story;
use Illuminate\Database\Seeder;

class StorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $story = Story::updateOrCreate(
            ['slug' => 'overgeared'],
            [
                'title' => 'Overgeared',
                'description' => 'Shin Youngwoo is a down-on-his-luck deadbeat in debt. His life changes in "Satisfy," the world\'s most popular VR game. While playing as a mediocre warrior named "Grid", he finds a magical book that unlocks "Pagma\'s Successor" - a rare, legendary blacksmith class.',
                'cover_path' => 'covers/overgeared.webp',
                'status' => 'completed',
                'source_url' => 'https://novelfull.net/overgeared/',
                'metadata' => ['source' => 'authorized-import'],
            ],
        );

        $author = Author::firstOrCreate(['slug' => 'park-saenal'], ['name' => 'Park Saenal']);
        $genres = collect(['Korean', 'LitRPG', 'Action', 'Virtual Reality', 'Crafting'])
            ->map(fn (string $name) => Genre::firstOrCreate(
                ['slug' => str($name)->slug()],
                ['name' => $name],
            ));

        $story->authors()->syncWithoutDetaching($author);
        $story->genres()->sync($genres->pluck('id')->all());
    }
}
