<?php

namespace Database\Factories;

use App\Models\ReadingProgress;
use App\Models\Chapter;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReadingProgress>
 */
class ReadingProgressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'story_id' => Story::factory(),
            'chapter_id' => Chapter::factory(),
            'scroll_percent' => fake()->numberBetween(0, 100),
            'last_read_at' => now(),
        ];
    }
}
