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

        $assassin = Story::updateOrCreate(
            ['slug' => 'the-reincarnated-assassin-is-a-genius-swordsman'],
            [
                'title' => 'The Reincarnated Assassin Is a Genius Swordsman',
                'description' => 'The Reincarnated Assassin is a Genius Swordsman follows Raon, a brainwashed assassin who was treated like a dog by the ruthless House Robert. After being betrayed and killed, he is reincarnated into the formidable Zieghart family as the grandson of the legendary "Destructive King".',
                'cover_path' => 'covers/the-reincarnated-assassin-is-a-genius-swordsman.webp',
                'status' => 'ongoing',
                'source_url' => 'https://novellunar.com/novel/the-reincarnated-assassin-is-a-genius-swordsman',
                'metadata' => ['source' => 'authorized-import'],
            ],
        );

        $assassinAuthor = Author::firstOrCreate(['slug' => 'voke-geulgaemi'], ['name' => 'VOKE (GeulGaemi)']);
        $assassinGenres = collect(['Action', 'Fantasy', 'Reincarnation'])
            ->map(fn (string $name) => Genre::firstOrCreate(
                ['slug' => str($name)->slug()],
                ['name' => $name],
            ));

        $assassin->authors()->syncWithoutDetaching($assassinAuthor);
        $assassin->genres()->sync($assassinGenres->pluck('id')->all());
    }
}
