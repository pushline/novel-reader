<?php

namespace Database\Factories;

use App\Models\Story;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Story>
 */
class StoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => str($title)->slug(),
            'description' => fake()->paragraphs(2, true),
            'status' => fake()->randomElement(['ongoing', 'completed', 'hiatus']),
            'source_url' => fake()->url(),
            'metadata' => ['language' => 'en'],
        ];
    }
}
