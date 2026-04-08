<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole(UserRole::Admin->value);

        $this->staff = User::factory()->create();
        $this->staff->assignRole(UserRole::Staff->value);
    }

    private function actingAsUser(User $user): static
    {
        return $this->withToken($user->createToken('test')->plainTextToken);
    }

    #[Test]
    public function admin_can_list_audit_logs(): void
    {
        // Trigger activity by creating a category (LogsActivity)
        $category = Category::factory()->create(['name' => 'Logged Category']);

        $response = $this->actingAsUser($this->admin)
            ->getJson('/api/v1/audit-logs');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'log_name',
                        'description',
                        'subject_type',
                        'subject_id',
                        'causer_type',
                        'causer_id',
                        'properties',
                        'created_at',
                    ],
                ],
                'meta',
                'links',
            ]);

        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    #[Test]
    public function staff_cannot_access_audit_logs(): void
    {
        $this->actingAsUser($this->staff)
            ->getJson('/api/v1/audit-logs')
            ->assertForbidden();
    }

    #[Test]
    public function unauthenticated_user_cannot_access_audit_logs(): void
    {
        $this->getJson('/api/v1/audit-logs')
            ->assertUnauthorized();
    }

    #[Test]
    public function can_filter_by_subject_type(): void
    {
        $category = Category::factory()->create();
        $category2 = Category::factory()->create();

        $response = $this->actingAsUser($this->admin)
            ->getJson('/api/v1/audit-logs?subject_type='.urlencode(Category::class));

        $response->assertOk();

        $data = $response->json('data');
        $this->assertNotEmpty($data);

        foreach ($data as $log) {
            $this->assertSame(Category::class, $log['subject_type']);
        }
    }

    #[Test]
    public function can_filter_by_subject_id(): void
    {
        $category = Category::factory()->create();
        $other = Category::factory()->create();

        $response = $this->actingAsUser($this->admin)
            ->getJson('/api/v1/audit-logs?subject_type='.urlencode(Category::class)."&subject_id={$category->id}");

        $response->assertOk();

        foreach ($response->json('data') as $log) {
            $this->assertSame($category->id, $log['subject_id']);
        }
    }

    #[Test]
    public function audit_logs_are_paginated(): void
    {
        // Trigger 5 log entries
        Category::factory()->count(5)->create();

        $response = $this->actingAsUser($this->admin)
            ->getJson('/api/v1/audit-logs?per_page=2');

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonCount(2, 'data');
    }
}
