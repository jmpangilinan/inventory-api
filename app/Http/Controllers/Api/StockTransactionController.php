<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockTransaction\ListStockTransactionRequest;
use App\Http\Requests\StockTransaction\StoreStockTransactionRequest;
use App\Http\Resources\StockTransactionResource;
use App\Services\StockTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class StockTransactionController extends Controller
{
    public function __construct(private readonly StockTransactionService $transactionService) {}

    #[OA\Get(
        path: '/products/{product}/transactions',
        operationId: 'stockTransactionsList',
        summary: 'List stock transactions for a product',
        security: [['bearerAuth' => []]],
        tags: ['Stock Transactions'],
        parameters: [
            new OA\Parameter(name: 'product', in: 'path', required: true, description: 'Product ID', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Items per page (1–100)', schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated transaction list',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/StockTransaction')),
                    new OA\Property(property: 'meta', type: 'object'),
                    new OA\Property(property: 'links', type: 'object'),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Product not found'),
        ]
    )]
    public function index(ListStockTransactionRequest $request, int $productId): AnonymousResourceCollection
    {
        $perPage = (int) $request->input('per_page', 15);

        return StockTransactionResource::collection(
            $this->transactionService->listByProduct($productId, $perPage)
        );
    }

    #[OA\Post(
        path: '/stock-transactions',
        operationId: 'stockTransactionsCreate',
        summary: 'Record a stock movement',
        description: 'Supports stock-in (purchase/return), stock-out (sale/damage/expired), and adjustments (correction/initial_stock). Insufficient stock on stock-out returns 422.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['product_id', 'type', 'reason', 'quantity'],
                properties: [
                    new OA\Property(property: 'product_id', type: 'integer', example: 1),
                    new OA\Property(property: 'type', type: 'string', enum: ['in', 'out', 'adjustment'], example: 'in'),
                    new OA\Property(property: 'reason', type: 'string', enum: ['purchase', 'return', 'device_scanned', 'sale', 'damage', 'expired', 'correction', 'initial_stock'], example: 'purchase'),
                    new OA\Property(property: 'quantity', type: 'integer', example: 20),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Restocking from supplier'),
                ]
            )
        ),
        tags: ['Stock Transactions'],
        responses: [
            new OA\Response(response: 201, description: 'Transaction recorded', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/StockTransaction')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error or insufficient stock', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
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
