<?php

declare(strict_types=1);

namespace App\Enums;

enum StockReason: string
{
    // Stock In
    case Purchase = 'purchase';
    case Return = 'return';
    case DeviceScanned = 'device_scanned';

    // Stock Out
    case Sale = 'sale';
    case Damage = 'damage';
    case Expired = 'expired';

    // Adjustment
    case Correction = 'correction';
    case InitialStock = 'initial_stock';
}
