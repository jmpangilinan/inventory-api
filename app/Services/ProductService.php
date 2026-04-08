<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\LowStockDetected;
use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    /** @return LengthAwarePaginator<int, Product> */
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Product> */
        return $this->productRepository->paginate($perPage);
    }

    public function findById(int $id): Product
    {
        /** @var Product */
        return $this->productRepository->findById($id);
    }

    /** @return Collection<int, Product> */
    public function getLowStock(): Collection
    {
        return $this->productRepository->findLowStock();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Product
    {
        /** @var Product */
        $product = $this->productRepository->create($data);

        if ($product->isLowStock()) {
            LowStockDetected::dispatch($product);
        }

        return $product;
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Product
    {
        /** @var Product */
        $product = $this->productRepository->update($id, $data);

        if ($product->isLowStock()) {
            LowStockDetected::dispatch($product);
        }

        return $product;
    }

    public function delete(int $id): bool
    {
        return $this->productRepository->delete($id);
    }
}
