<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Exceptions\NotFoundException;
use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    /** @return LengthAwarePaginator<int, Product> */
    public function paginateByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->where('category_id', $categoryId)
            ->with('category')
            ->latest()
            ->paginate($perPage);
    }

    /** @return Collection<int, Product> */
    public function findLowStock(): Collection
    {
        return Product::query()
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('is_active', true)
            ->with('category')
            ->get();
    }

    public function findBySku(string $sku): Product
    {
        $product = Product::query()->where('sku', $sku)->first();

        if (! $product) {
            throw new NotFoundException('Product');
        }

        return $product;
    }
}
