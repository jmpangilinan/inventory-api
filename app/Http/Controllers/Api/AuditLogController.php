<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListAuditLogRequest;
use App\Http\Resources\AuditLogResource;
use App\Services\AuditLogService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditLogController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index(ListAuditLogRequest $request): AnonymousResourceCollection
    {
        $logs = $this->auditLogService->paginate(
            $request->only(['subject_type', 'subject_id', 'causer_id']),
            (int) ($request->integer('per_page', 15)),
        );

        return AuditLogResource::collection($logs);
    }
}
