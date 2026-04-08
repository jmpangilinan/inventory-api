<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    #[OA\Post(
        path: '/auth/register',
        operationId: 'authRegister',
        summary: 'Register a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'secret123'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered successfully',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/User'),
                    new OA\Property(property: 'token', type: 'string', example: '1|abc123...'),
                ])
            ),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
        );

        return response()->json([
            'data' => new UserResource($result['user']),
            'token' => $result['token'],
        ], Response::HTTP_CREATED);
    }

    #[OA\Post(
        path: '/auth/login',
        operationId: 'authLogin',
        summary: 'Log in and receive a Bearer token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authenticated',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/User'),
                    new OA\Property(property: 'token', type: 'string', example: '1|abc123...'),
                ])
            ),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            email: $request->validated('email'),
            password: $request->validated('password'),
        );

        return response()->json([
            'data' => new UserResource($result['user']),
            'token' => $result['token'],
        ]);
    }

    #[OA\Post(
        path: '/auth/logout',
        operationId: 'authLogout',
        summary: 'Revoke the current Bearer token',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Logged out', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logged out successfully.']);
    }

    #[OA\Get(
        path: '/auth/me',
        operationId: 'authMe',
        summary: 'Get the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authenticated user',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/User')])
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()),
        ]);
    }
}
