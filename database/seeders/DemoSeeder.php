<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\StockReason;
use App\Enums\TransactionType;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => Hash::make('password')]
        );
        $admin->assignRole(UserRole::Admin->value);

        $staff = User::firstOrCreate(
            ['email' => 'staff@example.com'],
            ['name' => 'Staff User', 'password' => Hash::make('password')]
        );
        $staff->assignRole(UserRole::Staff->value);

        // Categories (slug mirrors CategoryService::create logic)
        $electronics = Category::create([
            'name' => 'Electronics',
            'slug' => Str::slug('Electronics'),
            'description' => 'Consumer electronics and accessories',
            'is_active' => true,
        ]);

        $furniture = Category::create([
            'name' => 'Furniture',
            'slug' => Str::slug('Furniture'),
            'description' => 'Office and home furniture',
            'is_active' => true,
        ]);

        $stationery = Category::create([
            'name' => 'Stationery',
            'slug' => Str::slug('Stationery'),
            'description' => 'Office supplies and stationery items',
            'is_active' => true,
        ]);

        // Products — healthy stock
        $laptop = Product::create([
            'sku' => 'ELEC-001',
            'name' => 'Laptop Pro 15"',
            'description' => '15-inch business laptop, Intel i7, 16GB RAM, 512GB SSD',
            'price' => 1299.99,
            'stock_quantity' => 45,
            'low_stock_threshold' => 10,
            'is_active' => true,
            'category_id' => $electronics->id,
        ]);

        $monitor = Product::create([
            'sku' => 'ELEC-002',
            'name' => '27" 4K Monitor',
            'description' => '27-inch 4K UHD IPS display, USB-C, 144Hz',
            'price' => 549.00,
            'stock_quantity' => 28,
            'low_stock_threshold' => 5,
            'is_active' => true,
            'category_id' => $electronics->id,
        ]);

        $keyboard = Product::create([
            'sku' => 'ELEC-003',
            'name' => 'Mechanical Keyboard',
            'description' => 'Compact TKL mechanical keyboard, Cherry MX Brown switches',
            'price' => 129.00,
            'stock_quantity' => 62,
            'low_stock_threshold' => 15,
            'is_active' => true,
            'category_id' => $electronics->id,
        ]);

        // Products — low stock
        $chair = Product::create([
            'sku' => 'FURN-001',
            'name' => 'Ergonomic Office Chair',
            'description' => 'Adjustable lumbar support, mesh back, 5-year warranty',
            'price' => 399.00,
            'stock_quantity' => 4,
            'low_stock_threshold' => 5,
            'is_active' => true,
            'category_id' => $furniture->id,
        ]);

        $desk = Product::create([
            'sku' => 'FURN-002',
            'name' => 'Standing Desk 160cm',
            'description' => 'Electric height-adjustable standing desk, memory presets',
            'price' => 699.00,
            'stock_quantity' => 3,
            'low_stock_threshold' => 5,
            'is_active' => true,
            'category_id' => $furniture->id,
        ]);

        // Products — out of stock
        $notebook = Product::create([
            'sku' => 'STAT-001',
            'name' => 'Premium Notebook A5',
            'description' => 'Hardcover dot-grid notebook, 240 pages, lay-flat binding',
            'price' => 18.50,
            'stock_quantity' => 0,
            'low_stock_threshold' => 20,
            'is_active' => true,
            'category_id' => $stationery->id,
        ]);

        $pen = Product::create([
            'sku' => 'STAT-002',
            'name' => 'Ballpoint Pen Set (12pk)',
            'description' => 'Smooth writing ballpoint pens, blue and black, 0.5mm',
            'price' => 8.99,
            'stock_quantity' => 150,
            'low_stock_threshold' => 30,
            'is_active' => true,
            'category_id' => $stationery->id,
        ]);

        // Stock transactions — build realistic history for laptop
        $initialStock = StockTransaction::create([
            'product_id' => $laptop->id,
            'user_id' => $admin->id,
            'type' => TransactionType::In,
            'reason' => StockReason::InitialStock,
            'quantity' => 50,
            'stock_before' => 0,
            'stock_after' => 50,
            'notes' => 'Initial stock intake',
        ]);

        $sale1 = StockTransaction::create([
            'product_id' => $laptop->id,
            'user_id' => $staff->id,
            'type' => TransactionType::Out,
            'reason' => StockReason::Sale,
            'quantity' => 3,
            'stock_before' => 50,
            'stock_after' => 47,
            'notes' => null,
        ]);

        $damage = StockTransaction::create([
            'product_id' => $laptop->id,
            'user_id' => $staff->id,
            'type' => TransactionType::Out,
            'reason' => StockReason::Damage,
            'quantity' => 2,
            'stock_before' => 47,
            'stock_after' => 45,
            'notes' => 'Damaged in transit — warehouse incident #WH-2024-11',
        ]);

        // Stock transaction for chair (low stock)
        StockTransaction::create([
            'product_id' => $chair->id,
            'user_id' => $admin->id,
            'type' => TransactionType::In,
            'reason' => StockReason::Purchase,
            'quantity' => 10,
            'stock_before' => 0,
            'stock_after' => 10,
            'notes' => 'Purchase order #PO-2024-089',
        ]);

        StockTransaction::create([
            'product_id' => $chair->id,
            'user_id' => $staff->id,
            'type' => TransactionType::Out,
            'reason' => StockReason::Sale,
            'quantity' => 6,
            'stock_before' => 10,
            'stock_after' => 4,
            'notes' => null,
        ]);

        // Adjustment for notebook (correction to 0)
        StockTransaction::create([
            'product_id' => $notebook->id,
            'user_id' => $admin->id,
            'type' => TransactionType::Adjustment,
            'reason' => StockReason::Correction,
            'quantity' => 0,
            'stock_before' => 5,
            'stock_after' => 0,
            'notes' => 'Physical count — all units found damaged, written off',
        ]);

        $this->command->info('Demo data seeded successfully.');
        $this->command->info('  admin@example.com  / password  (Admin)');
        $this->command->info('  staff@example.com  / password  (Staff)');
    }
}
