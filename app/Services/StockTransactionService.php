<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransactionType;
use App\Events\LowStockDetected;
use App\Events\StockTransactionRecorded;
use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use App\Repositories\Interfaces\StockTransactionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class StockTransactionService
{
    public function __construct(
        private readonly StockTransactionRepositoryInterface $transactionRepository,
        private readonly ProductService $productService,
    ) {}

    /** @return LengthAwarePaginator<int, StockTransaction> */
    public function listByProduct(int $productId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->transactionRepository->paginateByProduct($productId, $perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function record(array $data, User $actor): StockTransaction
    {
        $product = $this->productService->findById($data['product_id']);
        $type = TransactionType::from($data['type']);
        $quantity = (int) $data['quantity'];
        $stockBefore = $product->stock_quantity;

        $stockAfter = match ($type) {
            TransactionType::In => $stockBefore + $quantity,
            TransactionType::Out => $this->calculateStockOut($product, $stockBefore, $quantity),
            TransactionType::Adjustment => $quantity,
        };

        /** @var StockTransaction $transaction */
        $transaction = $this->transactionRepository->create([
            'product_id' => $product->id,
            'user_id' => $actor->id,
            'type' => $type->value,
            'reason' => $data['reason'],
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'notes' => $data['notes'] ?? null,
        ]);

        $product->update(['stock_quantity' => $stockAfter]);
        $product->refresh();

        StockTransactionRecorded::dispatch($transaction);

        if ($product->isLowStock()) {
            LowStockDetected::dispatch($product);
        }

        return $transaction->load(['product', 'user']);
    }

    private function calculateStockOut(Product $product, int $stockBefore, int $quantity): int
    {
        if ($quantity > $stockBefore) {
            throw new BusinessException(
                "Insufficient stock. Available: {$stockBefore}, requested: {$quantity}."
            );
        }

        return $stockBefore - $quantity;
    }
}
