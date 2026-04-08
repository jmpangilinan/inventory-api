<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-####-??')),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->optional()->sentence(),
            'price' => $this->faker->randomFloat(2, 1, 10000),
            'stock_quantity' => $this->faker->numberBetween(20, 100),
            'low_stock_threshold' => 10,
            'is_active' => true,
        ];
    }

    public function lowStock(): static
    {
        return $this->state([
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state([
            'stock_quantity' => 0,
            'low_stock_threshold' => 10,
        ]);
    }
}
