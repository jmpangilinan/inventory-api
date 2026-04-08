<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface BaseRepositoryInterface
{
    /** @return Collection<int, Model> */
    public function all(): Collection;

    /** @return LengthAwarePaginator<int, Model> */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): Model;

    /** @param array<string, mixed> $data */
    public function create(array $data): Model;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Model;

    public function delete(int $id): bool;
}
