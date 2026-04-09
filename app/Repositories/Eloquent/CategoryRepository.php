<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Exceptions\NotFoundException;
use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Category>
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Category::query()
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($perPage);
    }

    /** @return LengthAwarePaginator<int, Category> */
    public function paginateActive(int $perPage = 15): LengthAwarePaginator
    {
        return Category::query()
            ->where('is_active', true)
            ->latest()
            ->paginate($perPage);
    }

    public function findBySlug(string $slug): Category
    {
        $category = Category::query()->where('slug', $slug)->first();

        if (! $category) {
            throw new NotFoundException('Category');
        }

        return $category;
    }
}
