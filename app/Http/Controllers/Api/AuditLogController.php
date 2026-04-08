<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListAuditLogRequest;
use App\Http\Resources\AuditLogResource;
use App\Services\AuditLogService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class AuditLogController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    #[OA\Get(
        path: '/audit-logs',
        operationId: 'auditLogsList',
        summary: 'List audit logs (Admin only)',
        security: [['bearerAuth' => []]],
        tags: ['Audit Logs'],
        parameters: [
            new OA\Parameter(name: 'subject_type', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'subject_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'causer_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated audit log list'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden — Admin role required'),
        ]
    )]
    public function index(ListAuditLogRequest $request): AnonymousResourceCollection
    {
        $logs = $this->auditLogService->paginate(
            $request->only(['subject_type', 'subject_id', 'causer_id']),
            (int) ($request->integer('per_page', 15)),
        );

        return AuditLogResource::collection($logs);
    }
}
