<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeviceWebhookRequest;
use App\Http\Resources\StockTransactionResource;
use App\Services\DeviceDataService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class DeviceWebhookController extends Controller
{
    public function __construct(
        private readonly DeviceDataService $deviceDataService,
    ) {}

    #[OA\Post(
        path: '/device/webhook',
        operationId: 'deviceWebhook',
        summary: 'Receive a signed payload from a hardware device',
        description: 'No Bearer token required. The request must carry an HMAC-SHA256 signature in the `X-Device-Signature: sha256=<hmac>` header, computed over the raw JSON body using the shared `DEVICE_WEBHOOK_SECRET`. The transaction is recorded with `reason=device_scanned` under a system user.',
        tags: ['Device Webhook'],
        parameters: [
            new OA\Parameter(
                name: 'X-Device-Signature',
                in: 'header',
                required: true,
                description: 'HMAC-SHA256 signature: sha256=<hmac_of_raw_body>',
                schema: new OA\Schema(type: 'string', example: 'sha256=abc123...')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['device_id', 'device_type', 'product_id', 'quantity', 'type'],
                properties: [
                    new OA\Property(property: 'device_id', type: 'string', example: 'SCANNER-001'),
                    new OA\Property(property: 'device_type', type: 'string', enum: ['barcode_scanner', 'weighing_scale'], example: 'barcode_scanner'),
                    new OA\Property(property: 'product_id', type: 'integer', example: 1),
                    new OA\Property(property: 'quantity', type: 'integer', example: 5),
                    new OA\Property(property: 'type', type: 'string', enum: ['in', 'out', 'adjustment'], example: 'out'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Stock transaction recorded', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/StockTransaction')])),
            new OA\Response(response: 401, description: 'Missing or invalid device signature'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function __invoke(DeviceWebhookRequest $request): JsonResponse
    {
        $transaction = $this->deviceDataService->process($request->validated());

        return StockTransactionResource::make($transaction)
            ->response()
            ->setStatusCode(201);
    }
}
