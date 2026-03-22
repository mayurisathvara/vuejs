<?php

namespace App\Http\Controllers\Api\V1\Organization;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\CallLog;
use App\Services\ExclusionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallLogController extends Controller
{
    use ApiResponse;

    /**
     * Return paginated call logs for the authenticated organization.
     *
     * Query parameters:
     *  - start_date   (required) YYYY-MM-DD
     *  - end_date     (required) YYYY-MM-DD
     *  - call_type    (optional) inbound | outbound
     *  - call_status  (optional) Answered | Missed | No Answer
     *  - caller_id    (optional) filter by specific SIM / mobile number
     *  - team_name    (optional) filter by team name
     *  - per_page     (optional, default 30, max 100)
     *  - page         (optional, default 1)
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'start_date'  => 'required|date_format:Y-m-d',
            'end_date'    => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'call_type'   => 'nullable|string|in:inbound,outbound',
            'call_status' => 'nullable|string|max:50',
            'caller_id'   => 'nullable|string|max:30',
            'team_name'   => 'nullable|string|max:100',
            'per_page'    => 'nullable|integer|min:1|max:100',
            'page'        => 'nullable|integer|min:1',
        ]);

        $user           = $request->user();
        $organizationId = (int) $user->organization_id;
        $perPage        = $request->per_page ?? 30;

        $query = CallLog::select([
                'unique_id',
                'date',
                'time',
                'date_time',
                'caller_duration',
                'call_type',
                'call_status',
                'team_name',
                'name',
                'caller_id',
                'caller_number',
                'call_back',
            ])
            ->where('organization_id', $organizationId)
            ->whereBetween('date', [$request->start_date, $request->end_date]);

        if ($request->filled('call_type')) {
            $query->where('call_type', $request->call_type);
        }

        if ($request->filled('call_status')) {
            $query->where('call_status', $request->call_status);
        }

        if ($request->filled('caller_id')) {
            $query->where('caller_id', $request->caller_id);
        }

        if ($request->filled('team_name')) {
            $query->where('team_name', $request->team_name);
        }

        $query->orderBy('date_time', 'desc');

        ExclusionService::applyToQuery($query, $organizationId);

        $logs = $query->paginate($perPage);

        return $this->successResponse([
            'call_logs'  => $logs->items(),
            'pagination' => [
                'total'        => $logs->total(),
                'per_page'     => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'from'         => $logs->firstItem(),
                'to'           => $logs->lastItem(),
            ],
        ], 'Call logs fetched successfully.');
    }
}
