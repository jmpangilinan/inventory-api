<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Inventory API',
    description: 'Inventory & Stock Management REST API — Repository + Service pattern, domain events, HMAC device webhooks, audit trail.',
    contact: new OA\Contact(email: 'admin@inventory-api.local'),
)]
#[OA\Server(url: L5_SWAGGER_CONST_HOST, description: 'Local development server')]
#[OA\SecurityScheme(securityScheme: 'bearerAuth', type: 'http', scheme: 'bearer')]
#[OA\Tag(name: 'Auth', description: 'Authentication')]
#[OA\Tag(name: 'Categories', description: 'Category management')]
#[OA\Tag(name: 'Products', description: 'Product catalogue and low-stock alerts')]
#[OA\Tag(name: 'Stock Transactions', description: 'Stock-in, stock-out, and adjustments')]
#[OA\Tag(name: 'Device Webhook', description: 'HMAC-signed hardware device integration')]
#[OA\Tag(name: 'Audit Logs', description: 'Activity audit trail — Admin only')]
#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['admin']),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'Category',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Electronics'),
        new OA\Property(property: 'slug', type: 'string', example: 'electronics'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'Product',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'sku', type: 'string', example: 'PROD-001'),
        new OA\Property(property: 'name', type: 'string', example: 'Laptop'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 999.99),
        new OA\Property(property: 'stock_quantity', type: 'integer', example: 50),
        new OA\Property(property: 'low_stock_threshold', type: 'integer', example: 10),
        new OA\Property(property: 'is_low_stock', type: 'boolean', example: false),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'category', ref: '#/components/schemas/Category'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'StockTransaction',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'type', type: 'string', enum: ['in', 'out', 'adjustment'], example: 'in'),
        new OA\Property(property: 'reason', type: 'string', enum: ['purchase', 'return', 'device_scanned', 'sale', 'damage', 'expired', 'correction', 'initial_stock'], example: 'purchase'),
        new OA\Property(property: 'quantity', type: 'integer', example: 20),
        new OA\Property(property: 'stock_before', type: 'integer', example: 30),
        new OA\Property(property: 'stock_after', type: 'integer', example: 50),
        new OA\Property(property: 'notes', type: 'string', nullable: true),
        new OA\Property(property: 'product', ref: '#/components/schemas/Product'),
        new OA\Property(property: 'performed_by', ref: '#/components/schemas/User'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'AuditLog',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'log_name', type: 'string', example: 'default'),
        new OA\Property(property: 'description', type: 'string', example: 'created'),
        new OA\Property(property: 'subject_type', type: 'string', nullable: true, example: 'App\\Models\\Product'),
        new OA\Property(property: 'subject_id', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'causer_type', type: 'string', nullable: true, example: 'App\\Models\\User'),
        new OA\Property(property: 'causer_id', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'properties', type: 'object', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'MessageResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]
)]
#[OA\Schema(
    schema: 'ValidationError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(property: 'errors', type: 'object'),
    ]
)]
abstract class Controller
{
    //
}
