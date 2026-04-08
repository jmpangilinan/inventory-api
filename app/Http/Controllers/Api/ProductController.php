<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ListProductRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    #[OA\Get(
        path: '/products',
        operationId: 'productsList',
        summary: 'List all products (paginated, filterable)',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', description: 'Filter by name or SKU', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'category_id', in: 'query', description: 'Filter by category', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'is_active', in: 'query', description: 'Filter by active status', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Items per page (1–100)', schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated product list',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Product')),
                    new OA\Property(property: 'meta', type: 'object'),
                    new OA\Property(property: 'links', type: 'object'),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function index(ListProductRequest $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'category_id', 'is_active']);
        $perPage = (int) $request->input('per_page', 15);

        return ProductResource::collection($this->productService->list($filters, $perPage));
    }

    #[OA\Post(
        path: '/products',
        operationId: 'productsCreate',
        summary: 'Create a product',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['sku', 'name', 'price', 'category_id'],
                properties: [
                    new OA\Property(property: 'sku', type: 'string', example: 'PROD-001'),
                    new OA\Property(property: 'name', type: 'string', example: 'Laptop'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 999.99),
                    new OA\Property(property: 'stock_quantity', type: 'integer', default: 0, example: 50),
                    new OA\Property(property: 'low_stock_threshold', type: 'integer', default: 10, example: 5),
                    new OA\Property(property: 'is_active', type: 'boolean', default: true),
                    new OA\Property(property: 'category_id', type: 'integer', example: 1),
                ]
            )
        ),
        tags: ['Products'],
        responses: [
            new OA\Response(response: 201, description: 'Product created', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Product')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/products/{id}',
        operationId: 'productsShow',
        summary: 'Get a product',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Product details', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Product')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(int $id): ProductResource
    {
        return new ProductResource($this->productService->findById($id));
    }

    #[OA\Put(
        path: '/products/{id}',
        operationId: 'productsUpdate',
        summary: 'Update a product',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'sku', type: 'string'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'price', type: 'number', format: 'float'),
                new OA\Property(property: 'stock_quantity', type: 'integer'),
                new OA\Property(property: 'low_stock_threshold', type: 'integer'),
                new OA\Property(property: 'is_active', type: 'boolean'),
                new OA\Property(property: 'category_id', type: 'integer'),
            ])
        ),
        tags: ['Products'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Product updated', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Product')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function update(UpdateProductRequest $request, int $id): ProductResource
    {
        return new ProductResource($this->productService->update($id, $request->validated()));
    }

    #[OA\Delete(
        path: '/products/{id}',
        operationId: 'productsDelete',
        summary: 'Soft-delete a product',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Product deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $this->productService->delete($id);

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    #[OA\Get(
        path: '/products/low-stock',
        operationId: 'productsLowStock',
        summary: 'List products at or below their low-stock threshold',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Low-stock products',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Product')),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function lowStock(): AnonymousResourceCollection
    {
        return ProductResource::collection($this->productService->getLowStock());
    }
}
