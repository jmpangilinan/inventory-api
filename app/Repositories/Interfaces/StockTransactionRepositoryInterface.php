<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\StockTransaction;
use Illuminate\Pagination\LengthAwarePaginator;

interface StockTransactionRepositoryInterface extends BaseRepositoryInterface
{
    /** @return LengthAwarePaginator<int, StockTransaction> */
    public function paginateByProduct(int $productId, int $perPage = 15): LengthAwarePaginator;
}
