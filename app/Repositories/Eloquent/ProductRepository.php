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

    public function findByIdWithLock(int $id): Product
    {
        $product = Product::query()->lockForUpdate()->find($id);

        if (! $product) {
            throw new NotFoundException('Product');
        }

        return $product;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with('category')
            ->when(isset($filters['search']), fn ($q) => $q->where(function ($q) use ($filters): void {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('sku', 'like', "%{$filters['search']}%");
            }))
            ->when(isset($filters['category_id']), fn ($q) => $q->where('category_id', $filters['category_id']))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($perPage);
    }
}
