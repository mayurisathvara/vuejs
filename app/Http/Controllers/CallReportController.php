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
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class CallReportController extends Controller
{
    public function index(Request $request)
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
            'call_back',
            'per_page',
            'page',
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
            'call_back' => ['nullable', 'in:Y,N'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = $this->buildReportQuery($request);


        $perPage = (int) $request->get('per_page', 10);
        $paginator = $query->paginate($perPage);

        return response()->json($paginator);
    }

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

        // Users dropdown:
        // - When Organization is selected: load all users (roles user, manager) in that org.
        // - When Department is selected: narrow to that department.
        // - For manager role: only users in accessible departments (and only role=user).
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

        // For user role: return only their assigned SIMs (no org/dept filter needed)
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
            // For admin/organization/manager roles:
            // - If a user is selected, return only SIMs assigned to that user.
            // - Otherwise, return SIMs for the effective organization (optionally narrowed by department).

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

    public function export(Request $request)
    {
        $this->normalizeEmptyStringsToNull($request, [
            'format',
            'start_date_time',
            'end_date_time',
            'call_type',
            'call_status',
            'organization_id',
            'department_id',
            'user_id',
            'caller_number',
            'call_back',
        ]);

        $validator = Validator::make($request->all(), [
            'format' => ['required', 'in:csv,excel'],
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
            'call_back' => ['nullable', 'in:Y,N'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $format = $request->get('format');
        $query = $this->buildReportQuery($request);

        $timestamp = now()->format('Ymd_His');

        if ($format === 'csv') {
            $filename = "call_reports_{$timestamp}.csv";

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function () use ($query) {
                $out = fopen('php://output', 'w');

                fputcsv($out, [
                    'Date',
                    'Time',
                    'SIM (caller_id)',
                    'Caller Duration',
                    'Call Type',
                    'Call Status',
                    'Department',
                    'User',
                    'Customer Number',
                    'Return Call (Y/N)',
                ]);

                $query->orderByDesc('date_time')
                    ->lazy(1000)
                    ->each(function (CallLog $log) use ($out) {
                        $department = $log->department_name;

                        fputcsv($out, [
                            optional($log->date)->format('Y-m-d') ?: optional($log->date_time)->format('Y-m-d'),
                            $log->time ? (string) $log->time : optional($log->date_time)->format('H:i:s'),
                            $log->caller_id,
                            $log->caller_duration,
                            $log->call_type,
                            $log->call_status,
                            $department,
                            $log->name,
                            $log->caller_number,
                            $log->call_back,
                        ]);
                    });

                fclose($out);
            }, 200, $headers);
        }

        // Excel export without extra PHP extensions:
        // We return an HTML table with .xls content type which opens in Excel.
        $filename = "call_reports_{$timestamp}.xls";

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($query) {
            echo "<table border=\"1\">";
            echo "<thead><tr>";
            foreach ([
                'Date',
                'Time',
                'SIM (caller_id)',
                'Caller Duration',
                'Call Type',
                'Call Status',
                'Department',
                'User',
                'Customer Number',
                'Return Call (Y/N)',
            ] as $h) {
                echo '<th>' . htmlspecialchars($h, ENT_QUOTES, 'UTF-8') . '</th>';
            }
            echo "</tr></thead><tbody>";

            $query->orderByDesc('date_time')
                ->lazy(1000)
                ->each(function (CallLog $log) {
                    $department = $log->department_name;
                    $row = [
                        optional($log->date)->format('Y-m-d') ?: optional($log->date_time)->format('Y-m-d'),
                        $log->time ? (string) $log->time : optional($log->date_time)->format('H:i:s'),
                        $log->caller_id,
                        $log->caller_duration,
                        $log->call_type,
                        $log->call_status,
                        $department,
                        $log->name,
                        $log->caller_number,
                        $log->call_back,
                    ];

                    echo '<tr>';
                    foreach ($row as $cell) {
                        echo '<td>' . htmlspecialchars((string) ($cell ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                    }
                    echo '</tr>';
                });

            echo "</tbody></table>";
        }, 200, $headers);
    }

    private function buildReportQuery(Request $request): Builder
    {
        $authUser = auth()->user();
        $role = $authUser->role;

        $hasCallerIdColumn = Schema::hasColumn('call_logs', 'caller_id');

        $select = [
            'call_logs.id',
            'call_logs.organization_id',
            'call_logs.name',
            'call_logs.date',
            'call_logs.time',
            'call_logs.date_time',
            'call_logs.call_type',
            'call_logs.call_status',
            'call_logs.caller_number',
            'call_logs.caller_duration',
            'call_logs.department_name',
            'call_logs.call_back',
            'call_logs.created_at',
        ];

        if ($hasCallerIdColumn) {
            $select[] = 'call_logs.caller_id';
        }

        $query = CallLog::query()->select($select);

        if ($role === 'admin') {
            if ($request->filled('organization_id')) {
                $query->where('organization_id', (int) $request->organization_id);
            }
        } elseif ($role === 'user') {
            // For user role: filter by caller_id using their assigned SIMs.
            // Use COALESCE(sims.mobile, user_sims.mobile) so both assignment styles work.
            if (!$hasCallerIdColumn) {
                $query->whereRaw('1 = 0');
            } else {
                $userSimMobiles = $this->getUserSimMobilesForCallerId($authUser->id);
                if (!empty($userSimMobiles)) {
                    $query->whereIn('call_logs.caller_id', $userSimMobiles);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        } else {
            $query->where('organization_id', $authUser->organization_id);
        }

        if ($role === 'manager') {
            // For manager role: get SIMs from users in accessible departments via user_sims
            if (!$hasCallerIdColumn) {
                $query->whereRaw('1 = 0');
            } else {
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
            }
        }

        // If a specific user is selected (admin/org/manager), narrow results to SIMs assigned to that user.
        // This makes the Users dropdown effective while still respecting role-based access.
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
                    // organization role
                    $allowed = (int) $selectedUser->organization_id === (int) $authUser->organization_id;
                }
            }

            if (!$hasCallerIdColumn) {
                $query->whereRaw('1 = 0');
            } elseif ($allowed) {
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

        if ($request->filled('department_id')) {
            $departmentId = (int) $request->department_id;
            
            // Get SIMs from the selected department
            $departmentSimMobiles = Sim::where('department_id', $departmentId)
                ->pluck('mobile')
                ->filter()
                ->toArray();

            if ($hasCallerIdColumn && !empty($departmentSimMobiles)) {
                $query->where(function (Builder $q) use ($departmentSimMobiles, $departmentId) {
                    // Filter by caller_id (SIMs from the department)
                    $q->whereIn('call_logs.caller_id', $departmentSimMobiles);
                    
                    // Also check department_name for backward compatibility
                    $department = Department::select(['name'])->where('id', $departmentId)->first();
                    if ($department?->name) {
                        $q->orWhere('call_logs.department_name', $department->name);
                    }
                });
            } else {
                // If no SIMs in department, only check department_name
                $department = Department::select(['name'])->where('id', $departmentId)->first();
                if ($department?->name) {
                    $query->where('call_logs.department_name', $department->name);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        }

        if ($request->filled('sim_mobile')) {
            if ($hasCallerIdColumn) {
                $simMobiles = is_array($request->sim_mobile) ? $request->sim_mobile : [$request->sim_mobile];
                $simMobiles = array_filter(array_map('trim', $simMobiles));
                
                if (!empty($simMobiles)) {
                    $query->whereIn('call_logs.caller_id', $simMobiles);
                }
            } else {
                // UI may send sim_mobile; if caller_id is not available, avoid SQL errors.
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('caller_number')) {
            $query->where('call_logs.caller_number', 'like', '%' . trim((string) $request->caller_number) . '%');
        }

        if ($request->filled('call_back')) {
            $query->where('call_logs.call_back', $request->call_back);
        }

        return $query->orderByDesc('call_logs.date_time');
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
