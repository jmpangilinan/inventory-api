<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HealthTest extends TestCase
{
    #[Test]
    public function health_endpoint_returns_ok_with_database_status(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertOk()
            ->assertJson(['status' => 'ok', 'database' => 'ok']);
    }
}
