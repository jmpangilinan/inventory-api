<?php

declare(strict_types=1);

namespace App\Enums;

enum DeviceType: string
{
    case BarcodeScanner = 'barcode_scanner';
    case WeighingScale = 'weighing_scale';
}
