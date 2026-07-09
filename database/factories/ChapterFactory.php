<?php

namespace Database\Factories;

use App\Models\Chapter;
use App\Models\Story;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chapter>
 */
class ChapterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'story_id' => Story::factory(),
            'number' => fake()->unique()->numberBetween(1, 3000),
            'title' => 'Chapter '.fake()->numberBetween(1, 3000),
            'content' => '<p>'.fake()->paragraph().'</p><p>'.fake()->paragraph().'</p>',
            'word_count' => fake()->numberBetween(400, 3200),
            'source_url' => fake()->url(),
            'import_hash' => hash('sha256', fake()->uuid()),
            'imported_at' => now(),
        ];
    }
}
