<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Category>
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    /** @return LengthAwarePaginator<int, Category> */
    public function paginateActive(int $perPage = 15): LengthAwarePaginator;

    public function findBySlug(string $slug): Category;
}
