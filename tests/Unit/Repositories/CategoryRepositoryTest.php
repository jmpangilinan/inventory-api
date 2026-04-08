<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Exceptions\NotFoundException;
use App\Models\Category;
use App\Repositories\Eloquent\CategoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CategoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CategoryRepository(new Category);
    }

    #[Test]
    public function paginate_active_returns_only_active_categories(): void
    {
        Category::factory()->count(3)->create(['is_active' => true]);
        Category::factory()->count(2)->inactive()->create();

        $result = $this->repository->paginateActive();

        $this->assertSame(3, $result->total());
    }

    #[Test]
    public function find_by_slug_returns_correct_category(): void
    {
        $category = Category::factory()->create(['slug' => 'electronics']);

        $result = $this->repository->findBySlug('electronics');

        $this->assertSame($category->id, $result->id);
    }

    #[Test]
    public function find_by_slug_throws_not_found_exception_when_missing(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Category not found.');

        $this->repository->findBySlug('non-existent');
    }
}
