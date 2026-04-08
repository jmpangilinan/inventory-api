<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection($this->productService->list());
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(int $id): ProductResource
    {
        return new ProductResource($this->productService->findById($id));
    }

    public function update(UpdateProductRequest $request, int $id): ProductResource
    {
        return new ProductResource($this->productService->update($id, $request->validated()));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->productService->delete($id);

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    public function lowStock(): AnonymousResourceCollection
    {
        return ProductResource::collection($this->productService->getLowStock());
    }
}
