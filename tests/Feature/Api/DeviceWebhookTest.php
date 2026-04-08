<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\StockReason;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeviceWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $secret = 'test_webhook_secret';

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        config(['device.webhook_secret' => $this->secret]);

        $category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 50,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function signedPost(array $payload): TestResponse
    {
        $body = json_encode($payload);
        $signature = 'sha256='.hash_hmac('sha256', $body, $this->secret);

        return $this->postJson(
            '/api/v1/device/webhook',
            $payload,
            ['X-Device-Signature' => $signature],
        );
    }

    #[Test]
    public function valid_signed_request_records_stock_transaction(): void
    {
        $response = $this->signedPost([
            'device_id' => 'SCANNER-001',
            'device_type' => 'barcode_scanner',
            'product_id' => $this->product->id,
            'quantity' => 10,
            'type' => TransactionType::Out->value,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', TransactionType::Out->value)
            ->assertJsonPath('data.reason', StockReason::DeviceScanned->value)
            ->assertJsonPath('data.stock_before', 50)
            ->assertJsonPath('data.stock_after', 40);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 40,
        ]);
    }

    #[Test]
    public function device_stock_in_increases_stock(): void
    {
        $response = $this->signedPost([
            'device_id' => 'SCALE-002',
            'device_type' => 'weighing_scale',
            'product_id' => $this->product->id,
            'quantity' => 20,
            'type' => TransactionType::In->value,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.stock_after', 70);
    }

    #[Test]
    public function missing_signature_returns_401(): void
    {
        $this->postJson('/api/v1/device/webhook', [
            'device_id' => 'SCANNER-001',
            'device_type' => 'barcode_scanner',
            'product_id' => $this->product->id,
            'quantity' => 5,
            'type' => TransactionType::Out->value,
        ])->assertUnauthorized()
            ->assertJsonPath('message', 'Missing device signature.');
    }

    #[Test]
    public function tampered_signature_returns_401(): void
    {
        $this->postJson('/api/v1/device/webhook',
            [
                'device_id' => 'SCANNER-001',
                'device_type' => 'barcode_scanner',
                'product_id' => $this->product->id,
                'quantity' => 5,
                'type' => TransactionType::Out->value,
            ],
            ['X-Device-Signature' => 'sha256=fakesignature'],
        )->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid device signature.');
    }

    #[Test]
    public function invalid_payload_returns_422(): void
    {
        $body = json_encode([]);
        $signature = 'sha256='.hash_hmac('sha256', $body, $this->secret);

        $this->postJson(
            '/api/v1/device/webhook',
            [],
            ['X-Device-Signature' => $signature],
        )->assertUnprocessable()
            ->assertJsonValidationErrors(['device_id', 'device_type', 'product_id', 'quantity', 'type']);
    }

    #[Test]
    public function invalid_device_type_returns_422(): void
    {
        $payload = [
            'device_id' => 'DEVICE-001',
            'device_type' => 'unknown_device',
            'product_id' => $this->product->id,
            'quantity' => 5,
            'type' => TransactionType::Out->value,
        ];
        $body = json_encode($payload);
        $signature = 'sha256='.hash_hmac('sha256', $body, $this->secret);

        $this->postJson(
            '/api/v1/device/webhook',
            $payload,
            ['X-Device-Signature' => $signature],
        )->assertUnprocessable()
            ->assertJsonValidationErrors(['device_type']);
    }

    #[Test]
    public function creates_system_user_as_transaction_actor(): void
    {
        $this->signedPost([
            'device_id' => 'SCANNER-001',
            'device_type' => 'barcode_scanner',
            'product_id' => $this->product->id,
            'quantity' => 5,
            'type' => TransactionType::Out->value,
        ]);

        $this->assertDatabaseHas('users', ['email' => 'device@system.local']);
    }
}
