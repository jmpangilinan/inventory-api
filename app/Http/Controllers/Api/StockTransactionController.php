<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockTransaction\StoreStockTransactionRequest;
use App\Http\Resources\StockTransactionResource;
use App\Services\StockTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class StockTransactionController extends Controller
{
    public function __construct(private readonly StockTransactionService $transactionService) {}

    public function index(Request $request, int $productId): AnonymousResourceCollection
    {
        $transactions = $this->transactionService->listByProduct($productId);

        return StockTransactionResource::collection($transactions);
    }

    public function store(StoreStockTransactionRequest $request): JsonResponse
    {
        $transaction = $this->transactionService->record(
            data: $request->validated(),
            actor: $request->user(),
        );

        return (new StockTransactionResource($transaction))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
