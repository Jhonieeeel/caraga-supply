<?php

namespace Database\Factories;

use App\Models\Supply;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supply>
 */
class SupplyFactory extends Factory
{
    protected $model = Supply::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'category' => $this->faker->randomElement(['Supplies', 'Non-Food Item', 'Fuel', 'Others']),
            'unit' => $this->faker->randomElement([
                'pc',
                'pack',
                'sachet',
                'unit',
                'ream',
                'box',
                'set',
                'meter',
                'kg',
                'bag',
                'case',
                'kit',
                'lot',
                'bucket',
                'galon',
                'crate',
                'bottle',
            ])
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Supply $supply) {
            // Create 2-5 related stock entries for each supply
            \App\Models\Stock::factory()->count(rand(2, 5))->create([
                'supply_id' => $supply->id,
            ]);
        });
    }
}
