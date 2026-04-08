<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;

class AuditLogService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Activity>
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Activity::query()->latest();

        if (isset($filters['subject_type'])) {
            $query->where('subject_type', $filters['subject_type']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', (int) $filters['subject_id']);
        }

        if (isset($filters['causer_id'])) {
            $query->where('causer_id', (int) $filters['causer_id']);
        }

        return $query->paginate($perPage);
    }
}
