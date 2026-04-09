<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\ListCategoryRequest;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService) {}

    #[OA\Get(
        path: '/categories',
        operationId: 'categoriesList',
        summary: 'List all categories (paginated, filterable)',
        security: [['bearerAuth' => []]],
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'is_active', in: 'query', description: 'Filter by active status', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Items per page (1–100)', schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated category list',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Category')),
                    new OA\Property(property: 'meta', type: 'object'),
                    new OA\Property(property: 'links', type: 'object'),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function index(ListCategoryRequest $request): AnonymousResourceCollection
    {
        $filters = $request->only(['is_active']);
        $perPage = (int) $request->input('per_page', 15);

        return CategoryResource::collection($this->categoryService->list($filters, $perPage));
    }

    #[OA\Post(
        path: '/categories',
        operationId: 'categoriesCreate',
        summary: 'Create a category',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Electronics'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true),
                ]
            )
        ),
        tags: ['Categories'],
        responses: [
            new OA\Response(response: 201, description: 'Category created', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Category')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/categories/{id}',
        operationId: 'categoriesShow',
        summary: 'Get a category',
        security: [['bearerAuth' => []]],
        tags: ['Categories'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Category details', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Category')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(int $id): CategoryResource
    {
        $category = $this->categoryService->findById($id);

        return new CategoryResource($category);
    }

    #[OA\Put(
        path: '/categories/{id}',
        operationId: 'categoriesUpdate',
        summary: 'Update a category',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Updated Name'),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'is_active', type: 'boolean'),
            ])
        ),
        tags: ['Categories'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Category updated', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Category')])),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function update(UpdateCategoryRequest $request, int $id): CategoryResource
    {
        $category = $this->categoryService->update($id, $request->validated());

        return new CategoryResource($category);
    }

    #[OA\Delete(
        path: '/categories/{id}',
        operationId: 'categoriesDelete',
        summary: 'Soft-delete a category',
        security: [['bearerAuth' => []]],
        tags: ['Categories'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Category deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $this->categoryService->delete($id);

        return response()->json(['message' => 'Category deleted successfully.']);
    }
}
