<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StockReason;
use App\Enums\TransactionType;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockTransaction>
 */
class StockTransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stockBefore = $this->faker->numberBetween(10, 100);
        $quantity = $this->faker->numberBetween(1, 10);

        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'type' => TransactionType::In->value,
            'reason' => StockReason::Purchase->value,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockBefore + $quantity,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function stockOut(): static
    {
        return $this->state(function (array $attributes): array {
            $stockBefore = $attributes['stock_before'];
            $quantity = min($attributes['quantity'], $stockBefore);

            return [
                'type' => TransactionType::Out->value,
                'reason' => StockReason::Sale->value,
                'quantity' => $quantity,
                'stock_after' => $stockBefore - $quantity,
            ];
        });
    }
}
