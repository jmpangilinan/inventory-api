<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $admin->assignRole(UserRole::Admin->value);

        $staff = User::factory()->create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => 'password',
        ]);
        $staff->assignRole(UserRole::Staff->value);
    }
}
