<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeviceWebhookRequest;
use App\Http\Resources\StockTransactionResource;
use App\Services\DeviceDataService;
use Illuminate\Http\JsonResponse;

class DeviceWebhookController extends Controller
{
    public function __construct(
        private readonly DeviceDataService $deviceDataService,
    ) {}

    public function __invoke(DeviceWebhookRequest $request): JsonResponse
    {
        $transaction = $this->deviceDataService->process($request->validated());

        return StockTransactionResource::make($transaction)
            ->response()
            ->setStatusCode(201);
    }
}
