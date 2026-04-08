<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use App\Repositories\Eloquent\StockTransactionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StockTransactionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private StockTransactionRepository $repository;

    private Product $product;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new StockTransactionRepository(new StockTransaction);
        $category = Category::factory()->create();
        $this->product = Product::factory()->create(['category_id' => $category->id]);
        $this->user = User::factory()->create();
    }

    #[Test]
    public function paginate_by_product_returns_only_that_products_transactions(): void
    {
        StockTransaction::factory()->count(3)->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
        ]);

        $other = Product::factory()->create(['category_id' => $this->product->category_id]);
        StockTransaction::factory()->count(2)->create([
            'product_id' => $other->id,
            'user_id' => $this->user->id,
        ]);

        $result = $this->repository->paginateByProduct($this->product->id);

        $this->assertSame(3, $result->total());
    }
}
