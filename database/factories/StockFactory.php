<?php

namespace Database\Factories;

use App\Models\Stock;
use App\Models\Supply;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
class StockFactory extends Factory
{

    protected $model = Stock::class;

    public function definition(): array
    {
        return [
            'supply_id' => Supply::factory(),
            'barcode' => $this->faker->unique()->ean13(),
            'stock_number' => strtoupper($this->faker->bothify('STOCK-####')),
            'quantity' => $this->faker->numberBetween(10, 100),
            'price' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }
}
