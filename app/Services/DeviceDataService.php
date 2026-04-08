<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\StockReason;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DeviceDataService
{
    public function __construct(
        private readonly StockTransactionService $stockTransactionService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function process(array $data): StockTransaction
    {
        return $this->stockTransactionService->record(
            [
                'product_id' => $data['product_id'],
                'type' => $data['type'],
                'reason' => StockReason::DeviceScanned->value,
                'quantity' => $data['quantity'],
                'notes' => $data['notes'] ?? "Device: {$data['device_id']} ({$data['device_type']})",
            ],
            $this->resolveSystemUser(),
        );
    }

    private function resolveSystemUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'device@system.local'],
            [
                'name' => 'Device System',
                'password' => Hash::make(Str::random(32)),
            ],
        );
    }
}
