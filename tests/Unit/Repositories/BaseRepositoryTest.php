<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Exceptions\NotFoundException;
use App\Models\User;
use App\Repositories\Eloquent\BaseRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BaseRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private BaseRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        // Concrete anonymous implementation for testing the abstract base
        $this->repository = new class(new User) extends BaseRepository {};
    }

    #[Test]
    public function all_returns_all_records(): void
    {
        User::factory()->count(3)->create();

        $result = $this->repository->all();

        $this->assertCount(3, $result);
    }

    #[Test]
    public function paginate_returns_paginated_results(): void
    {
        User::factory()->count(20)->create();

        $result = $this->repository->paginate(10);

        $this->assertCount(10, $result->items());
        $this->assertSame(20, $result->total());
    }

    #[Test]
    public function find_by_id_returns_correct_model(): void
    {
        $user = User::factory()->create();

        $result = $this->repository->findById($user->id);

        $this->assertSame($user->id, $result->id);
    }

    #[Test]
    public function find_by_id_throws_not_found_exception_when_missing(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('User not found.');

        $this->repository->findById(999);
    }

    #[Test]
    public function create_persists_and_returns_model(): void
    {
        $data = collect(User::factory()->make()->toArray())
            ->only(['name', 'email'])
            ->put('password', 'password')
            ->all();

        $result = $this->repository->create($data);

        $this->assertDatabaseHas('users', ['email' => $data['email']]);
        $this->assertInstanceOf(User::class, $result);
    }

    #[Test]
    public function update_modifies_and_returns_fresh_model(): void
    {
        $user = User::factory()->create();

        $result = $this->repository->update($user->id, ['name' => 'Updated Name']);

        $this->assertSame('Updated Name', $result->name);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
    }

    #[Test]
    public function delete_removes_record_from_database(): void
    {
        $user = User::factory()->create();

        $result = $this->repository->delete($user->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
