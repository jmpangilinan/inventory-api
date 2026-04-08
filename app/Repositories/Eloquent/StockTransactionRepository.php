<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\StockTransaction;
use App\Repositories\Interfaces\StockTransactionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class StockTransactionRepository extends BaseRepository implements StockTransactionRepositoryInterface
{
    public function __construct(StockTransaction $model)
    {
        parent::__construct($model);
    }

    /** @return LengthAwarePaginator<int, StockTransaction> */
    public function paginateByProduct(int $productId, int $perPage = 15): LengthAwarePaginator
    {
        return StockTransaction::query()
            ->where('product_id', $productId)
            ->with(['product', 'user'])
            ->latest()
            ->paginate($perPage);
    }
}
