<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function is_low_stock_returns_true_when_at_or_below_threshold(): void
    {
        $product = Product::factory()->make([
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
        ]);

        $this->assertTrue($product->isLowStock());
    }

    #[Test]
    public function is_low_stock_returns_true_when_exactly_at_threshold(): void
    {
        $product = Product::factory()->make([
            'stock_quantity' => 10,
            'low_stock_threshold' => 10,
        ]);

        $this->assertTrue($product->isLowStock());
    }

    #[Test]
    public function is_low_stock_returns_false_when_above_threshold(): void
    {
        $product = Product::factory()->make([
            'stock_quantity' => 50,
            'low_stock_threshold' => 10,
        ]);

        $this->assertFalse($product->isLowStock());
    }

    #[Test]
    public function product_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertSame($category->id, $product->category->id);
    }
}
