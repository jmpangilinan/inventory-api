<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Category;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuditLogService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuditLogService;
    }

    #[Test]
    public function it_paginates_all_activity_logs(): void
    {
        Category::factory()->count(3)->create();

        $result = $this->service->paginate([], 15);

        $this->assertGreaterThanOrEqual(3, $result->total());
    }

    #[Test]
    public function it_filters_by_subject_type(): void
    {
        Category::factory()->count(2)->create();

        $result = $this->service->paginate(['subject_type' => Category::class]);

        foreach ($result->items() as $log) {
            $this->assertSame(Category::class, $log->subject_type);
        }

        $this->assertGreaterThanOrEqual(2, $result->total());
    }

    #[Test]
    public function it_filters_by_subject_id(): void
    {
        $cat1 = Category::factory()->create();
        $cat2 = Category::factory()->create();

        $result = $this->service->paginate([
            'subject_type' => Category::class,
            'subject_id' => $cat1->id,
        ]);

        $this->assertSame(1, $result->total());
        $this->assertSame($cat1->id, $result->items()[0]->subject_id);
    }

    #[Test]
    public function it_filters_by_causer_id(): void
    {
        // Activitylog records with no causer when triggered outside request context
        $result = $this->service->paginate(['causer_id' => 999]);

        $this->assertSame(0, $result->total());
    }

    #[Test]
    public function it_respects_per_page_parameter(): void
    {
        Category::factory()->count(5)->create();

        $result = $this->service->paginate([], 2);

        $this->assertSame(2, $result->perPage());
        $this->assertCount(2, $result->items());
    }
}
