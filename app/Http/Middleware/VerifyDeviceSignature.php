<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\UnauthorizedException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyDeviceSignature
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-Device-Signature');

        if (! $signature) {
            throw new UnauthorizedException('Missing device signature.');
        }

        $secret = config('device.webhook_secret');
        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);

        if (! hash_equals($expected, $signature)) {
            throw new UnauthorizedException('Invalid device signature.');
        }

        return $next($request);
    }
}
