<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Device Webhook Secret
    |--------------------------------------------------------------------------
    |
    | Shared secret used to verify HMAC-SHA256 signatures from hardware
    | devices (barcode scanners, weighing scales). Devices must send
    | X-Device-Signature: sha256=<hmac> computed over the raw request body.
    |
    */
    'webhook_secret' => env('DEVICE_WEBHOOK_SECRET', ''),
];
