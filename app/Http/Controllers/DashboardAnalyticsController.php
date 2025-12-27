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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class DashboardAnalyticsController extends Controller
{
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

        $baseQuery = $this->buildDashboardQuery($request);

        $totalCalls = (clone $baseQuery)->count();
        $answeredCalls = (clone $baseQuery)->where('call_logs.call_status', 'Answered')->count();

        $outboundCalls = (clone $baseQuery)->where('call_logs.call_type', 'outbound')->count();
        $inboundCalls = (clone $baseQuery)->where('call_logs.call_type', 'inbound')->count();
        $missedCalls = (clone $baseQuery)->where('call_logs.call_status', 'Missed')->count();

        $uniqueCalls = (clone $baseQuery)
            ->whereNotNull('call_logs.caller_number')
            ->where('call_logs.caller_number', '!=', '')
            ->distinct('call_logs.caller_number')
            ->count('call_logs.caller_number');

        $answerRatePct = $totalCalls > 0 ? (int) round(($answeredCalls / $totalCalls) * 100) : 0;

        $avgSeconds = (clone $baseQuery)
            ->whereNotNull('call_logs.caller_duration')
            ->selectRaw('AVG(TIME_TO_SEC(call_logs.caller_duration)) as avg_seconds')
            ->value('avg_seconds');

        $avgSeconds = $avgSeconds !== null ? (int) round((float) $avgSeconds) : 0;

        $outboundAnswered = (clone $baseQuery)
            ->where('call_logs.call_type', 'outbound')
            ->where('call_logs.call_status', 'Answered')
            ->count();

        $outboundNoAnswer = (clone $baseQuery)
            ->where('call_logs.call_type', 'outbound')
            ->where('call_logs.call_status', 'No Answer')
            ->count();

        $inboundAnswered = (clone $baseQuery)
            ->where('call_logs.call_type', 'inbound')
            ->where('call_logs.call_status', 'Answered')
            ->count();

        $inboundMissed = (clone $baseQuery)
            ->where('call_logs.call_type', 'inbound')
            ->where('call_logs.call_status', 'Missed')
            ->count();

        // Missed Calls Analysis (Returned vs Callback Pending)
        // Convention: call_back = 'Y' means callback/return completed, otherwise pending.
        $missedReturned = (clone $baseQuery)
            ->where('call_logs.call_status', 'Missed')
            ->where('call_logs.call_back', 'Y')
            ->count();

        $missedPending = (clone $baseQuery)
            ->where('call_logs.call_status', 'Missed')
            ->where(function ($q) {
                $q->whereNull('call_logs.call_back')
                    ->orWhere('call_logs.call_back', '!=', 'Y');
            })
            ->count();

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

        return response()->json([
            'total_calls' => $totalCalls,
            'answer_rate_pct' => $answerRatePct,
            'avg_duration_seconds' => $avgSeconds,
            'avg_duration_display' => $this->formatDurationShort($avgSeconds),
            'outbound_calls' => $outboundCalls,
            'inbound_calls' => $inboundCalls,
            'missed_calls' => $missedCalls,
            'unique_calls' => $uniqueCalls,
            'missed_analysis' => [
                'returned_calls' => $missedReturned,
                'callback_pending' => $missedPending,
            ],
            'peak_hours' => $peakHours,
            'breakdown' => [
                'outbound' => [
                    'answered' => $outboundAnswered,
                    'no_answer' => $outboundNoAnswer,
                    'total' => $outboundCalls,
                ],
                'inbound' => [
                    'answered' => $inboundAnswered,
                    'missed' => $inboundMissed,
                    'total' => $inboundCalls,
                ],
            ],
        ]);
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

        $baseQuery = $this->buildDashboardQuery($request);

        $rows = (clone $baseQuery)
            ->selectRaw('DATE(call_logs.date_time) as day')
            ->selectRaw("SUM(CASE WHEN call_logs.call_type = 'inbound' THEN 1 ELSE 0 END) as inbound_calls")
            ->selectRaw("SUM(CASE WHEN call_logs.call_type = 'outbound' THEN 1 ELSE 0 END) as outbound_calls")
            ->selectRaw('COUNT(*) as total_calls')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

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

        $hasCallerIdColumn = Schema::hasColumn('call_logs', 'caller_id');

        $query = CallLog::query();

        // Role scoping
        if ($role === 'admin') {
            if ($request->filled('organization_id')) {
                $query->where('call_logs.organization_id', (int) $request->organization_id);
            }
        } elseif ($role === 'user') {
            // Only their assigned SIMs
            if ($hasCallerIdColumn) {
                $userSimMobiles = $this->getUserSimMobilesForCallerId($authUser->id);

                if (!empty($userSimMobiles)) {
                    $query->whereIn('call_logs.caller_id', $userSimMobiles);
                } else {
                    $query->whereRaw('1 = 0');
                }
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
                    $userIds = User::whereIn('department_id', $accessible)
                        ->where('role', 'user')
                        ->pluck('id')
                        ->toArray();

                    if (!empty($userIds)) {
                        $managerSimMobiles = $this->getUserSimMobilesForCallerIdMany($userIds);

                        if (!empty($managerSimMobiles)) {
                            $query->whereIn('call_logs.caller_id', $managerSimMobiles);
                        } else {
                            $query->whereRaw('1 = 0');
                        }
                    } else {
                        $query->whereRaw('1 = 0');
                    }
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

            $departmentSimMobiles = Sim::where('department_id', $departmentId)
                ->pluck('mobile')
                ->filter()
                ->toArray();

            if ($hasCallerIdColumn && !empty($departmentSimMobiles)) {
                $query->where(function (Builder $q) use ($departmentSimMobiles, $departmentId) {
                    $q->whereIn('call_logs.caller_id', $departmentSimMobiles);

                    $department = Department::select(['name'])->where('id', $departmentId)->first();
                    if ($department?->name) {
                        $q->orWhere('call_logs.department_name', $department->name);
                    }
                });
            } else {
                $department = Department::select(['name'])->where('id', $departmentId)->first();
                if ($department?->name) {
                    $query->where('call_logs.department_name', $department->name);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        }

        // Selected user filter (admin/org/manager)
        if ($role !== 'user' && $request->filled('user_id')) {
            $selectedUserId = (int) $request->user_id;
            $selectedUser = User::query()
                ->select(['id', 'organization_id', 'department_id', 'role'])
                ->where('id', $selectedUserId)
                ->first();

            $allowed = false;
            if ($selectedUser) {
                if ($role === 'admin') {
                    $allowed = true;
                } elseif ($role === 'manager') {
                    $accessible = $this->getManagerAccessibleDepartmentIds($authUser);
                    $allowed = (int) $selectedUser->organization_id === (int) $authUser->organization_id
                        && $selectedUser->role === 'user'
                        && in_array((int) $selectedUser->department_id, $accessible, true);
                } else {
                    $allowed = (int) $selectedUser->organization_id === (int) $authUser->organization_id;
                }
            }

            if ($allowed && $hasCallerIdColumn) {
                $userSimMobiles = $this->getUserSimMobilesForCallerId($selectedUserId);

                if (!empty($userSimMobiles)) {
                    $query->whereIn('call_logs.caller_id', $userSimMobiles);
                } else {
                    $query->whereRaw('1 = 0');
                }
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

    private function getUserSimMobilesForCallerId(int $userId): array
    {
        return UserSim::query()
            ->leftJoin('sims', 'sims.id', '=', 'user_sims.sim_id')
            ->where('user_sims.user_id', $userId)
            ->selectRaw('COALESCE(sims.mobile, user_sims.mobile) as mobile')
            ->pluck('mobile')
            ->filter()
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '')
            ->unique()
            ->values()
            ->toArray();
    }

    private function getUserSimMobilesForCallerIdMany(array $userIds): array
    {
        $userIds = array_values(array_filter(array_map('intval', $userIds)));
        if (empty($userIds)) {
            return [];
        }

        return UserSim::query()
            ->leftJoin('sims', 'sims.id', '=', 'user_sims.sim_id')
            ->whereIn('user_sims.user_id', $userIds)
            ->selectRaw('COALESCE(sims.mobile, user_sims.mobile) as mobile')
            ->pluck('mobile')
            ->filter()
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '')
            ->unique()
            ->values()
            ->toArray();
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
