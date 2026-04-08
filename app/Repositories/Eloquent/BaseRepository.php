<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Exceptions\NotFoundException;
use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

abstract class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(protected readonly Model $model) {}

    /** @return Collection<int, Model> */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /** @return LengthAwarePaginator<int, Model> */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()->latest()->paginate($perPage);
    }

    public function findById(int $id): Model
    {
        $model = $this->model->find($id);

        if (! $model) {
            throw new NotFoundException(class_basename($this->model));
        }

        return $model;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Model
    {
        $model = $this->findById($id);
        $model->update($data);

        return $model->fresh();
    }

    public function delete(int $id): bool
    {
        $model = $this->findById($id);

        return $model->delete();
    }
}
