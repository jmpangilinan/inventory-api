<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /** @return LengthAwarePaginator<int, Product> */
    public function paginateByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator;

    /** @return Collection<int, Product> */
    public function findLowStock(): Collection;

    public function findBySku(string $sku): Product;

    public function findByIdWithLock(int $id): Product;

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;
}
