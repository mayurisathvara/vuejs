<?php

namespace App\Http\Controllers;

use App\Models\CallLog;
use App\Models\Department;
use App\Models\Organization;
use App\Models\Sim;
use App\Models\User;
use App\Models\UserSim;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class DashboardAnalyticsController extends Controller
{
    private const DASHBOARD_CACHE_TTL_SECONDS = 30;

    public function options(Request $request)
    {
        $this->normalizeEmptyStringsToNull($request, [
            'organization_id',
            'department_id',
            'user_id',
        ]);

        $validator = Validator::make($request->all(), [
            'organization_id' => ['nullable', 'integer'],
            'department_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $authUser = auth()->user();
        $role = $authUser->role;

        $effectiveOrganizationId = null;
        if ($role === 'admin') {
            $effectiveOrganizationId = $request->get('organization_id');
        } else {
            $effectiveOrganizationId = $authUser->organization_id;
        }

        $organizations = [];
        if ($role === 'admin') {
            $organizations = Organization::where('status', 'active')
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();
        }

        $departments = collect();
        if ($effectiveOrganizationId) {
            $departmentsQuery = Department::where('organization_id', $effectiveOrganizationId)
                ->select(['id', 'name'])
                ->orderBy('name');

            if ($role === 'manager') {
                $accessible = $this->getManagerAccessibleDepartmentIds($authUser);
                if (!empty($accessible)) {
                    $departmentsQuery->whereIn('id', $accessible);
                } else {
                    $departmentsQuery->whereRaw('1 = 0');
                }
            }

            $departments = $departmentsQuery->get();
        }

        $users = collect();
        if ($effectiveOrganizationId) {
            $usersQuery = User::whereIn('role', ['user', 'manager'])
                ->select(['id', 'name', 'department_id', 'organization_id', 'role'])
                ->orderBy('name')
                ->where('organization_id', $effectiveOrganizationId);

            if ($request->filled('department_id')) {
                $usersQuery->where('department_id', (int) $request->department_id);
            }

            if ($role === 'manager') {
                $accessible = $this->getManagerAccessibleDepartmentIds($authUser);
                if (!empty($accessible)) {
                    $usersQuery->whereIn('department_id', $accessible);
                } else {
                    $usersQuery->whereRaw('1 = 0');
                }
                $usersQuery->where('role', 'user');
            }

            $users = $usersQuery->get();
        }

        // SIMs options:
        // - User role: only their SIMs
        // - Admin/Org/Manager: org SIMs, optionally narrowed by department or selected user
        if ($role === 'user') {
            $sims = UserSim::query()
                ->leftJoin('sims', 'sims.id', '=', 'user_sims.sim_id')
                ->where('user_sims.user_id', $authUser->id)
                ->selectRaw('COALESCE(sims.mobile, user_sims.mobile) as mobile')
                ->addSelect([
                    'sims.id as id',
                    'sims.name as name',
                    'sims.department_id as department_id',
                ])
                ->orderBy('mobile')
                ->get()
                ->unique('mobile')
                ->values()
                ->map(fn ($row) => [
                    'id' => $row->id,
                    'mobile' => $row->mobile,
                    'name' => $row->name,
                    'department_id' => $row->department_id,
                ]);
        } elseif ($effectiveOrganizationId) {
            $selectedUserId = $request->filled('user_id') ? (int) $request->user_id : null;

            if ($selectedUserId) {
                $selectedUser = User::query()
                    ->select(['id', 'organization_id', 'department_id', 'role'])
                    ->where('id', $selectedUserId)
                    ->first();

                $allowed = false;
                if ($selectedUser && (int) $selectedUser->organization_id === (int) $effectiveOrganizationId) {
                    if ($role === 'admin') {
                        $allowed = true;
                    } elseif ($role === 'manager') {
                        $accessible = $this->getManagerAccessibleDepartmentIds($authUser);
                        $allowed = $selectedUser->role === 'user' && in_array((int) $selectedUser->department_id, $accessible, true);
                    } else {
                        // organization role
                        $allowed = true;
                    }
                }

                if ($allowed) {
                    $sims = UserSim::query()
                        ->leftJoin('sims', 'sims.id', '=', 'user_sims.sim_id')
                        ->where('user_sims.user_id', $selectedUserId)
                        ->selectRaw('COALESCE(sims.mobile, user_sims.mobile) as mobile')
                        ->addSelect([
                            'sims.id as id',
                            'sims.name as name',
                            'sims.department_id as department_id',
                        ])
                        ->orderBy('mobile')
                        ->get()
                        ->unique('mobile')
                        ->values()
                        ->map(fn ($row) => [
                            'id' => $row->id,
                            'mobile' => $row->mobile,
                            'name' => $row->name,
                            'department_id' => $row->department_id,
                        ]);
                } else {
                    $sims = collect();
                }
            } else {
                $simsQuery = Sim::where('organization_id', $effectiveOrganizationId)
                    ->select(['id', 'mobile', 'name', 'organization_id', 'department_id']);

                if ($request->filled('department_id')) {
                    $simsQuery->where('department_id', (int) $request->department_id);
                }

                $sims = $simsQuery
                    ->orderBy('mobile')
                    ->get()
                    ->map(fn ($sim) => [
                        'id' => $sim->id,
                        'mobile' => $sim->mobile,
                        'name' => $sim->name,
                        'department_id' => $sim->department_id,
                    ]);
            }
        } else {
            $sims = collect();
        }

        return response()->json([
            'organizations' => $organizations,
            'departments' => $departments,
            'users' => $users,
            'sims' => $sims,
            'effective_organization_id' => $effectiveOrganizationId,
        ]);
    }

    public function summary(Request $request)
    {
        $this->normalizeEmptyStringsToNull($request, [
            'start_date_time',
            'end_date_time',
            'call_type',
            'call_status',
            'organization_id',
            'department_id',
            'user_id',
            'caller_number',
        ]);

        $validator = Validator::make($request->all(), [
            'start_date_time' => ['nullable', 'date'],
            'end_date_time' => ['nullable', 'date'],
            'call_type' => ['nullable', 'in:inbound,outbound'],
            'call_status' => ['nullable', 'in:Answered,No Answer,Missed'],
            'organization_id' => ['nullable', 'integer'],
            'department_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
            'sim_mobile' => ['nullable', 'array'],
            'sim_mobile.*' => ['string', 'max:25'],
            'caller_number' => ['nullable', 'string', 'max:25'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $authUser = auth()->user();
        $cacheKey = $this->dashboardCacheKey($authUser->id, 'summary', $request->all());

        $payload = Cache::remember($cacheKey, self::DASHBOARD_CACHE_TTL_SECONDS, function () use ($request) {
            // Normalize/ensure a stable date range (dashboard UI sends full-day ranges)
            $start = $request->filled('start_date_time')
                ? Carbon::parse($request->start_date_time)->startOfDay()
                : Carbon::now()->startOfDay();

            $end = $request->filled('end_date_time')
                ? Carbon::parse($request->end_date_time)->endOfDay()
                : Carbon::now()->endOfDay();

            if ($start->greaterThan($end)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }

            $periodDays = $start->diffInDays($end) + 1;
            $prevEnd = $start->copy()->subDay()->endOfDay();
            $prevStart = $start->copy()->subDays($periodDays)->startOfDay();

            $currentRequest = $request->duplicate();
            $currentRequest->merge([
                'start_date_time' => $start->toDateTimeString(),
                'end_date_time' => $end->toDateTimeString(),
            ]);

            $previousRequest = $request->duplicate();
            $previousRequest->merge([
                'start_date_time' => $prevStart->toDateTimeString(),
                'end_date_time' => $prevEnd->toDateTimeString(),
            ]);

            $baseQuery = $this->buildDashboardQuery($currentRequest);
            $previousBaseQuery = $this->buildDashboardQuery($previousRequest);

            // Aggregate query for summary metrics (current period)
            $row = (clone $baseQuery)
                ->selectRaw('COUNT(*) as total_calls')
                ->selectRaw("SUM(CASE WHEN call_logs.call_status = 'Answered' THEN 1 ELSE 0 END) as answered_calls")
                ->selectRaw("SUM(CASE WHEN call_logs.call_type = 'outbound' THEN 1 ELSE 0 END) as outbound_calls")
                ->selectRaw("SUM(CASE WHEN call_logs.call_type = 'inbound' THEN 1 ELSE 0 END) as inbound_calls")
                ->selectRaw("SUM(CASE WHEN call_logs.call_status = 'Missed' THEN 1 ELSE 0 END) as missed_calls")
                ->selectRaw("SUM(CASE WHEN call_logs.call_type = 'outbound' AND call_logs.call_status = 'Answered' THEN 1 ELSE 0 END) as outbound_answered")
                ->selectRaw("SUM(CASE WHEN call_logs.call_type = 'outbound' AND call_logs.call_status = 'No Answer' THEN 1 ELSE 0 END) as outbound_no_answer")
                ->selectRaw("SUM(CASE WHEN call_logs.call_type = 'inbound' AND call_logs.call_status = 'Answered' THEN 1 ELSE 0 END) as inbound_answered")
                ->selectRaw("SUM(CASE WHEN call_logs.call_type = 'inbound' AND call_logs.call_status = 'Missed' THEN 1 ELSE 0 END) as inbound_missed")
                ->selectRaw("SUM(CASE WHEN call_logs.call_status = 'Missed' AND call_logs.call_back = 'Y' THEN 1 ELSE 0 END) as missed_returned")
                ->selectRaw("SUM(CASE WHEN call_logs.call_status = 'Missed' AND (call_logs.call_back IS NULL OR call_logs.call_back <> 'Y') THEN 1 ELSE 0 END) as missed_pending")
                ->selectRaw("COUNT(DISTINCT NULLIF(TRIM(call_logs.caller_number), '')) as unique_calls")
                ->selectRaw("AVG(TIME_TO_SEC(NULLIF(call_logs.caller_duration, ''))) as avg_seconds")
                ->first();

            // Same aggregates for previous period (for change %)
            $prevRow = (clone $previousBaseQuery)
                ->selectRaw('COUNT(*) as total_calls')
                ->selectRaw("SUM(CASE WHEN call_logs.call_status = 'Answered' THEN 1 ELSE 0 END) as answered_calls")
                ->selectRaw("SUM(CASE WHEN call_logs.call_type = 'outbound' THEN 1 ELSE 0 END) as outbound_calls")
                ->selectRaw("SUM(CASE WHEN call_logs.call_type = 'inbound' THEN 1 ELSE 0 END) as inbound_calls")
                ->selectRaw("SUM(CASE WHEN call_logs.call_status = 'Missed' THEN 1 ELSE 0 END) as missed_calls")
                ->selectRaw("COUNT(DISTINCT NULLIF(TRIM(call_logs.caller_number), '')) as unique_calls")
                ->selectRaw("AVG(TIME_TO_SEC(NULLIF(call_logs.caller_duration, ''))) as avg_seconds")
                ->first();

            $totalCalls = (int) ($row->total_calls ?? 0);
            $answeredCalls = (int) ($row->answered_calls ?? 0);
            $outboundCalls = (int) ($row->outbound_calls ?? 0);
            $inboundCalls = (int) ($row->inbound_calls ?? 0);
            $missedCalls = (int) ($row->missed_calls ?? 0);
            $uniqueCalls = (int) ($row->unique_calls ?? 0);
            $avgSeconds = $row->avg_seconds !== null ? (int) round((float) $row->avg_seconds) : 0;

            $prevTotalCalls = (int) ($prevRow->total_calls ?? 0);
            $prevAnsweredCalls = (int) ($prevRow->answered_calls ?? 0);
            $prevOutboundCalls = (int) ($prevRow->outbound_calls ?? 0);
            $prevInboundCalls = (int) ($prevRow->inbound_calls ?? 0);
            $prevMissedCalls = (int) ($prevRow->missed_calls ?? 0);
            $prevUniqueCalls = (int) ($prevRow->unique_calls ?? 0);
            $prevAvgSeconds = $prevRow->avg_seconds !== null ? (int) round((float) $prevRow->avg_seconds) : 0;

            $answerRate = $totalCalls > 0 ? ($answeredCalls / $totalCalls) * 100 : 0;
            $prevAnswerRate = $prevTotalCalls > 0 ? ($prevAnsweredCalls / $prevTotalCalls) * 100 : 0;

            $answerRatePct = (int) round($answerRate);

            // Change % (mobile dashboard logic)
            $totalCallsChange = $this->percentChange($totalCalls, $prevTotalCalls);
            $answerRateChange = $this->percentChange($answerRate, $prevAnswerRate);
            $avgDurationChange = $this->percentChange($avgSeconds, $prevAvgSeconds);
            $outboundChange = $this->percentChange($outboundCalls, $prevOutboundCalls);
            $inboundChange = $this->percentChange($inboundCalls, $prevInboundCalls);
            $missedChange = $this->percentChange($missedCalls, $prevMissedCalls);
            $uniqueChange = $this->percentChange($uniqueCalls, $prevUniqueCalls);

            // Peak Call Hours: top 2 two-hour windows
            $peakBuckets = (clone $baseQuery)
                ->whereNotNull('call_logs.date_time')
                ->selectRaw('FLOOR(HOUR(call_logs.date_time) / 2) as bucket')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('bucket')
                ->orderByDesc('total')
                ->limit(2)
                ->get();

            $peakMax = $peakBuckets->max('total') ?: 0;
            $peakHours = $peakBuckets->map(function ($row) use ($peakMax) {
                $bucket = (int) ($row->bucket ?? 0);
                $count = (int) ($row->total ?? 0);
                $startHour = $bucket * 2;
                $endHour = $startHour + 2;

                $label = sprintf('%s – %s', $this->formatHourRangeLabel($startHour), $this->formatHourRangeLabel($endHour));
                $pct = $peakMax > 0 ? (int) round(($count / $peakMax) * 100) : 0;

                return [
                    'label' => $label,
                    'count' => $count,
                    'pct' => $pct,
                ];
            })->values();

            return [
                'total_calls' => $totalCalls,
                'total_calls_change_pct' => $totalCallsChange,
                'total_calls_change_label' => $this->formatChangeLabel($totalCallsChange),
                'answer_rate_pct' => $answerRatePct,
                'answer_rate_change_pct' => $answerRateChange,
                'answer_rate_change_label' => $this->formatChangeLabel($answerRateChange),
                'avg_duration_seconds' => $avgSeconds,
                'avg_duration_display' => $this->formatDurationShort($avgSeconds),
                'avg_duration_change_pct' => $avgDurationChange,
                'avg_duration_change_label' => $this->formatChangeLabel($avgDurationChange),
                'outbound_calls' => $outboundCalls,
                'outbound_calls_change_pct' => $outboundChange,
                'outbound_calls_change_label' => $this->formatChangeLabel($outboundChange),
                'inbound_calls' => $inboundCalls,
                'inbound_calls_change_pct' => $inboundChange,
                'inbound_calls_change_label' => $this->formatChangeLabel($inboundChange),
                'missed_calls' => $missedCalls,
                'missed_calls_change_pct' => $missedChange,
                'missed_calls_change_label' => $this->formatChangeLabel($missedChange),
                'unique_calls' => $uniqueCalls,
                'unique_calls_change_pct' => $uniqueChange,
                'unique_calls_change_label' => $this->formatChangeLabel($uniqueChange),
                'missed_analysis' => [
                    'returned_calls' => (int) ($row->missed_returned ?? 0),
                    'callback_pending' => (int) ($row->missed_pending ?? 0),
                ],
                'peak_hours' => $peakHours,
                'breakdown' => [
                    'outbound' => [
                        'answered' => (int) ($row->outbound_answered ?? 0),
                        'no_answer' => (int) ($row->outbound_no_answer ?? 0),
                        'total' => $outboundCalls,
                    ],
                    'inbound' => [
                        'answered' => (int) ($row->inbound_answered ?? 0),
                        'missed' => (int) ($row->inbound_missed ?? 0),
                        'total' => $inboundCalls,
                    ],
                ],
                'period' => [
                    'start_date_time' => $start->toDateTimeString(),
                    'end_date_time' => $end->toDateTimeString(),
                    'previous_start_date_time' => $prevStart->toDateTimeString(),
                    'previous_end_date_time' => $prevEnd->toDateTimeString(),
                ],
            ];
        });

        return response()->json($payload);
    }

    private function formatHourRangeLabel(int $hour24): string
    {
        $h = $hour24 % 24;
        if ($h < 0) {
            $h += 24;
        }

        $suffix = $h >= 12 ? 'PM' : 'AM';
        $h12 = $h % 12;
        if ($h12 === 0) {
            $h12 = 12;
        }

        return sprintf('%d %s', $h12, $suffix);
    }

    public function dailyCallVolume(Request $request)
    {
        $this->normalizeEmptyStringsToNull($request, [
            'start_date_time',
            'end_date_time',
            'organization_id',
            'department_id',
            'user_id',
            'caller_number',
        ]);

        $validator = Validator::make($request->all(), [
            'start_date_time' => ['nullable', 'date'],
            'end_date_time' => ['nullable', 'date'],
            'organization_id' => ['nullable', 'integer'],
            'department_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
            'sim_mobile' => ['nullable', 'array'],
            'sim_mobile.*' => ['string', 'max:25'],
            'caller_number' => ['nullable', 'string', 'max:25'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Default range: last 7 days (inclusive) if not provided
        if (!$request->filled('start_date_time') && !$request->filled('end_date_time')) {
            $end = Carbon::now()->endOfDay();
            $start = Carbon::now()->subDays(6)->startOfDay();
            $request->merge([
                'start_date_time' => $start->toDateTimeString(),
                'end_date_time' => $end->toDateTimeString(),
            ]);
        }

        $start = $request->filled('start_date_time')
            ? Carbon::parse($request->start_date_time)->startOfDay()
            : Carbon::now()->subDays(6)->startOfDay();

        $end = $request->filled('end_date_time')
            ? Carbon::parse($request->end_date_time)->endOfDay()
            : Carbon::now()->endOfDay();

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        // Ensure date range is applied consistently
        $request->merge([
            'start_date_time' => $start->toDateTimeString(),
            'end_date_time' => $end->toDateTimeString(),
        ]);

        $authUser = auth()->user();
        $cacheKey = $this->dashboardCacheKey($authUser->id, 'daily-call-volume', $request->all());

        $rows = Cache::remember($cacheKey, self::DASHBOARD_CACHE_TTL_SECONDS, function () use ($request) {
            $baseQuery = $this->buildDashboardQuery($request);

            return (clone $baseQuery)
                ->selectRaw('DATE(call_logs.date_time) as day')
                ->selectRaw("SUM(CASE WHEN call_logs.call_type = 'inbound' THEN 1 ELSE 0 END) as inbound_calls")
                ->selectRaw("SUM(CASE WHEN call_logs.call_type = 'outbound' THEN 1 ELSE 0 END) as outbound_calls")
                ->selectRaw('COUNT(*) as total_calls')
                ->groupBy('day')
                ->orderBy('day')
                ->get();
        });

        $byDay = [];
        foreach ($rows as $row) {
            $dayKey = (string) $row->day;
            $byDay[$dayKey] = [
                'inbound' => (int) ($row->inbound_calls ?? 0),
                'outbound' => (int) ($row->outbound_calls ?? 0),
                'total' => (int) ($row->total_calls ?? 0),
            ];
        }

        $labels = [];
        $inbound = [];
        $outbound = [];
        $total = [];

        $cursor = $start->copy()->startOfDay();
        $endDay = $end->copy()->startOfDay();
        while ($cursor->lessThanOrEqualTo($endDay)) {
            $dayKey = $cursor->toDateString();
            $labels[] = $dayKey;

            $inbound[] = (int) ($byDay[$dayKey]['inbound'] ?? 0);
            $outbound[] = (int) ($byDay[$dayKey]['outbound'] ?? 0);
            $total[] = (int) ($byDay[$dayKey]['total'] ?? 0);

            $cursor->addDay();
        }

        $daysCount = count($labels);
        $sumTotal = array_sum($total);
        $avgPerDay = $daysCount > 0 ? (int) round($sumTotal / $daysCount) : 0;

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                'total' => $total,
                'inbound' => $inbound,
                'outbound' => $outbound,
            ],
            'meta' => [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'days' => $daysCount,
                'avg_per_day' => $avgPerDay,
            ],
        ]);
    }

    private function buildDashboardQuery(Request $request): Builder
    {
        $authUser = auth()->user();
        $role = $authUser->role;

        static $hasCallerIdColumn = null;
        if ($hasCallerIdColumn === null) {
            $hasCallerIdColumn = Schema::hasColumn('call_logs', 'caller_id');
        }

        $query = CallLog::query();

        // Role scoping
        if ($role === 'admin') {
            if ($request->filled('organization_id')) {
                $query->where('call_logs.organization_id', (int) $request->organization_id);
            }
        } elseif ($role === 'user') {
            // Only their assigned SIMs (use subquery to avoid large PHP arrays)
            if ($hasCallerIdColumn) {
                $query->whereIn('call_logs.caller_id', $this->userSimMobilesSubquery($authUser->id));
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            $query->where('call_logs.organization_id', $authUser->organization_id);
        }

        if ($role === 'manager') {
            if ($hasCallerIdColumn) {
                $accessible = $this->getManagerAccessibleDepartmentIds($authUser);
                if (!empty($accessible)) {
                    $query->whereIn('call_logs.caller_id', $this->managerSimMobilesSubquery($authUser->organization_id, $accessible));
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Date range
        if ($request->filled('start_date_time') && $request->filled('end_date_time')) {
            $start = Carbon::parse($request->start_date_time);
            $end = Carbon::parse($request->end_date_time);
            $query->whereBetween('call_logs.date_time', [$start, $end]);
        } elseif ($request->filled('start_date_time')) {
            $start = Carbon::parse($request->start_date_time);
            $query->where('call_logs.date_time', '>=', $start);
        } elseif ($request->filled('end_date_time')) {
            $end = Carbon::parse($request->end_date_time);
            $query->where('call_logs.date_time', '<=', $end);
        }

        if ($request->filled('call_type')) {
            $query->where('call_logs.call_type', $request->call_type);
        }

        if ($request->filled('call_status')) {
            $query->where('call_logs.call_status', $request->call_status);
        }

        // Department filter
        if ($request->filled('department_id')) {
            $departmentId = (int) $request->department_id;

            $departmentName = Department::query()
                ->where('id', $departmentId)
                ->value('name');

            if ($hasCallerIdColumn) {
                $simMobilesSub = Sim::query()
                    ->where('department_id', $departmentId)
                    ->whereNotNull('mobile')
                    ->where('mobile', '!=', '')
                    ->select('mobile');

                $query->where(function (Builder $q) use ($simMobilesSub, $departmentName) {
                    $q->whereIn('call_logs.caller_id', $simMobilesSub);
                    if (!empty($departmentName)) {
                        $q->orWhere('call_logs.department_name', $departmentName);
                    }
                });
            } else {
                if (!empty($departmentName)) {
                    $query->where('call_logs.department_name', $departmentName);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        }

        // Selected user filter (admin/org/manager)
        if ($role !== 'user' && $request->filled('user_id')) {
            $selectedUserId = (int) $request->user_id;

            $allowed = false;
            if ($role === 'admin') {
                $allowed = User::query()->where('id', $selectedUserId)->exists();
            } elseif ($role === 'manager') {
                $accessible = $this->getManagerAccessibleDepartmentIds($authUser);
                $allowed = !empty($accessible) && User::query()
                    ->where('id', $selectedUserId)
                    ->where('organization_id', $authUser->organization_id)
                    ->where('role', 'user')
                    ->whereIn('department_id', $accessible)
                    ->exists();
            } else {
                $allowed = User::query()
                    ->where('id', $selectedUserId)
                    ->where('organization_id', $authUser->organization_id)
                    ->exists();
            }

            if ($allowed && $hasCallerIdColumn) {
                $query->whereIn('call_logs.caller_id', $this->userSimMobilesSubquery($selectedUserId));
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // SIM filter
        if ($request->filled('sim_mobile') && $hasCallerIdColumn) {
            $simMobiles = is_array($request->sim_mobile) ? $request->sim_mobile : [$request->sim_mobile];
            $simMobiles = array_filter(array_map('trim', $simMobiles));
            if (!empty($simMobiles)) {
                $query->whereIn('call_logs.caller_id', $simMobiles);
            }
        }

        // Customer number filter
        if ($request->filled('caller_number')) {
            $query->where('call_logs.caller_number', 'like', '%' . trim((string) $request->caller_number) . '%');
        }

        return $query;
    }

    private function userSimMobilesSubquery(int $userId): Builder
    {
        return UserSim::query()
            ->leftJoin('sims', 'sims.id', '=', 'user_sims.sim_id')
            ->where('user_sims.user_id', $userId)
            ->whereRaw("TRIM(COALESCE(sims.mobile, user_sims.mobile)) <> ''")
            ->selectRaw('DISTINCT TRIM(COALESCE(sims.mobile, user_sims.mobile))');
    }

    private function managerSimMobilesSubquery(int $organizationId, array $departmentIds): Builder
    {
        $departmentIds = array_values(array_filter(array_map('intval', $departmentIds)));
        if (empty($departmentIds)) {
            // empty set
            return UserSim::query()->selectRaw('NULL')->whereRaw('1 = 0');
        }

        return UserSim::query()
            ->join('users', 'users.id', '=', 'user_sims.user_id')
            ->leftJoin('sims', 'sims.id', '=', 'user_sims.sim_id')
            ->where('users.organization_id', $organizationId)
            ->where('users.role', 'user')
            ->whereIn('users.department_id', $departmentIds)
            ->whereRaw("TRIM(COALESCE(sims.mobile, user_sims.mobile)) <> ''")
            ->selectRaw('DISTINCT TRIM(COALESCE(sims.mobile, user_sims.mobile))');
    }

    private function dashboardCacheKey(int $userId, string $endpoint, array $params): string
    {
        // Keep key stable and small
        ksort($params);
        $hash = sha1(json_encode($params));
        return "dashboard:${endpoint}:u${userId}:${hash}";
    }

    private function formatDurationShort(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0s';
        }

        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        if ($h > 0) {
            return sprintf('%dh %dm', $h, $m);
        }

        if ($m > 0) {
            return sprintf('%dm %ds', $m, $s);
        }

        return sprintf('%ds', $s);
    }

    /**
     * Percent change helper (mobile dashboard logic).
     * Returns int between -100 and +100.
     */
    private function percentChange($current, $previous): int
    {
        $cur = (float) $current;
        $prev = (float) $previous;

        if ($prev == 0.0) {
            if ($cur == 0.0) {
                return 0;
            }
            return 100;
        }

        $change = (($cur - $prev) / $prev) * 100;

        if ($change > 100) {
            $change = 100;
        } elseif ($change < -100) {
            $change = -100;
        }

        return (int) round($change);
    }

    /**
     * Format change label with sign and percent (e.g. "+12%", "-5%", "0%")
     */
    private function formatChangeLabel($percent): string
    {
        $p = (int) $percent;
        if ($p > 0) {
            return '+' . $p . '%';
        }
        if ($p < 0) {
            return (string) $p . '%';
        }
        return '0%';
    }

    private function getManagerAccessibleDepartmentIds($authUser): array
    {
        $accessibleDepartments = [];

        if ($authUser->department_id) {
            $accessibleDepartments[] = $authUser->department_id;
        }

        if ($authUser->allowed_department_ids) {
            $allowedDepartments = is_string($authUser->allowed_department_ids)
                ? json_decode($authUser->allowed_department_ids, true)
                : $authUser->allowed_department_ids;

            if (!empty($allowedDepartments) && is_array($allowedDepartments)) {
                $accessibleDepartments = array_merge($accessibleDepartments, $allowedDepartments);
            }
        }

        return array_values(array_unique(array_filter($accessibleDepartments)));
    }

    private function normalizeEmptyStringsToNull(Request $request, array $keys): void
    {
        $updates = [];
        foreach ($keys as $key) {
            if ($request->has($key) && $request->input($key) === '') {
                $updates[$key] = null;
            }
        }

        if (!empty($updates)) {
            $request->merge($updates);
        }
    }
}
