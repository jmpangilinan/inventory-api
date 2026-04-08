<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Exceptions\NotFoundException;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\Eloquent\ProductRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepository $repository;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductRepository(new Product);
        $this->category = Category::factory()->create();
    }

    #[Test]
    public function paginate_by_category_returns_products_for_category(): void
    {
        Product::factory()->count(3)->create(['category_id' => $this->category->id]);
        $other = Category::factory()->create();
        Product::factory()->count(2)->create(['category_id' => $other->id]);

        $result = $this->repository->paginateByCategory($this->category->id);

        $this->assertSame(3, $result->total());
    }

    #[Test]
    public function find_low_stock_returns_products_at_or_below_threshold(): void
    {
        Product::factory()->count(2)->lowStock()->create(['category_id' => $this->category->id]);
        Product::factory()->count(3)->create(['category_id' => $this->category->id, 'stock_quantity' => 50]);

        $result = $this->repository->findLowStock();

        $this->assertCount(2, $result);
    }

    #[Test]
    public function find_by_sku_returns_correct_product(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-TEST-001',
        ]);

        $result = $this->repository->findBySku('SKU-TEST-001');

        $this->assertSame($product->id, $result->id);
    }

    #[Test]
    public function find_by_sku_throws_not_found_exception_when_missing(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Product not found.');

        $this->repository->findBySku('NON-EXISTENT');
    }
}
