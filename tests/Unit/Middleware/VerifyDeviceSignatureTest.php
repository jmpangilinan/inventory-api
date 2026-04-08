<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Exceptions\UnauthorizedException;
use App\Http\Middleware\VerifyDeviceSignature;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VerifyDeviceSignatureTest extends TestCase
{
    private VerifyDeviceSignature $middleware;

    private string $secret = 'test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new VerifyDeviceSignature;
        config(['device.webhook_secret' => $this->secret]);
    }

    #[Test]
    public function it_passes_with_valid_signature(): void
    {
        $body = json_encode(['product_id' => 1, 'quantity' => 5]);
        $signature = 'sha256='.hash_hmac('sha256', $body, $this->secret);

        $request = Request::create('/device/webhook', 'POST', content: $body);
        $request->headers->set('X-Device-Signature', $signature);
        $request->headers->set('Content-Type', 'application/json');

        $called = false;
        $this->middleware->handle($request, function () use (&$called) {
            $called = true;

            return response()->json([]);
        });

        $this->assertTrue($called);
    }

    #[Test]
    public function it_rejects_missing_signature(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Missing device signature.');

        $request = Request::create('/device/webhook', 'POST');

        $this->middleware->handle($request, fn () => response()->json([]));
    }

    #[Test]
    public function it_rejects_invalid_signature(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Invalid device signature.');

        $body = json_encode(['product_id' => 1]);
        $request = Request::create('/device/webhook', 'POST', content: $body);
        $request->headers->set('X-Device-Signature', 'sha256=tampered_value');

        $this->middleware->handle($request, fn () => response()->json([]));
    }
}
