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
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\StockTransactionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StockTransactionService
{
    public function __construct(
        private readonly StockTransactionRepositoryInterface $transactionRepository,
        private readonly ProductRepositoryInterface $productRepository,
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
        $type = TransactionType::from($data['type']);
        $quantity = (int) $data['quantity'];

        /** @var StockTransaction $transaction */
        $transaction = DB::transaction(function () use ($data, $actor, $type, $quantity): StockTransaction {
            // Lock the product row for the duration of this transaction to
            // prevent concurrent requests from reading stale stock_quantity.
            $product = $this->productRepository->findByIdWithLock($data['product_id']);
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

            return $transaction;
        });

        $transaction->load(['product', 'user']);

        StockTransactionRecorded::dispatch($transaction);

        if ($transaction->product->isLowStock()) {
            LowStockDetected::dispatch($transaction->product);
        }

        return $transaction;
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
