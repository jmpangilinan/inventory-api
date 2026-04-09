<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Category>
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->categoryRepository->paginateWithFilters($filters, $perPage);
    }

    public function findById(int $id): Category
    {
        /** @var Category */
        return $this->categoryRepository->findById($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Category
    {
        $data['slug'] = Str::slug($data['name']);

        /** @var Category */
        return $this->categoryRepository->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Category
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        /** @var Category */
        return $this->categoryRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->categoryRepository->delete($id);
    }
}
