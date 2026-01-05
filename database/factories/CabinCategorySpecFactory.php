<?php

namespace Database\Factories;

use App\Models\Cruise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CabinCategorySpec>
 */
class CabinCategorySpecFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'category_type' => fake()->randomElement(['Interior', 'Ocean View', 'Balcony', 'Suite']),
            'category_code' => substr(fake()->word(), 0, 2),
            'category_name' => fake()->randomElement([
                'Standard Interior',
                'Ocean View',
                'Promenade',
                'Spacious Ocean View',
            ]),
            'capacity' => fake()->numberBetween(2, 6),
            'description' => [
                'de' => sprintf(
                    'Zwei %s, die zu einem %s kombiniert werden können, ein %s und ein %s. Kabinengröße: %.2f m<sup>2</sup>',
                    fake()->word(),
                    fake()->word(),
                    fake()->word(),
                    fake()->word(),
                    fake()->randomFloat(2, 12, 25),
                ),
                'en' => sprintf(
                    'Two %s that convert to a %s, one %s and a %s. (%.0f ft<sup>2</sup> / %.2f m<sup>2</sup>).',
                    fake()->word(),
                    fake()->word(),
                    fake()->word(),
                    fake()->word(),
                    fake()->numberBetween(140, 270),
                    fake()->randomFloat(2, 12, 25),
                ),
                'es' => sprintf(
                    'Dos %s convertibles en una %s, una %s y %s. Camarote: %.2f m<sup>2</sup>.',
                    fake()->word(),
                    fake()->word(),
                    fake()->word(),
                    fake()->word(),
                    fake()->randomFloat(2, 12, 25),
                ),
            ],
            'iframe' => implode('_', fake()->words(2)) . '.jpg',
            'images' => json_encode(['interior_k_3.gif']),
            'decks' => function () {
                $type = fake()->randomElement(['single', 'list', 'range']);

                if ($type === 'list') {
                    $numbers = collect(range(7, 12))
                        ->random(fake()->numberBetween(2, 4))
                        ->sort()
                        ->unique()
                        ->values()
                        ->toArray();
                    return implode(', ', $numbers);
                }

                if ($type === 'range') {
                    $start = fake()->numberBetween(7, 10);
                    $end = fake()->numberBetween($start + 1, 12);
                    return "{$start} - {$end}";
                }

                return (string) fake()->numberBetween(7, 12);
            },
            'display_order' => fake()->numberBetween(1, 100),
            'cruise_id' => Cruise::factory(),
            'category_number' => fake()->numberBetween(1, 4),
        ];
    }
}
