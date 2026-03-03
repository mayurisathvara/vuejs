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

class SummaryReportController extends Controller
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
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $authUser = auth()->user();
        $role = $authUser->role;

        // Get all SIM mobiles that the user has access to
        $accessibleSimMobiles = $this->getAccessibleSimMobiles($authUser, $request);

        if (empty($accessibleSimMobiles)) {
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'per_page' => (int) $request->get('per_page', 10),
                'total' => 0,
                'last_page' => 1,
            ]);
        }

        // Build the summary query
        $summaryData = $this->buildSummaryQuery($request, $accessibleSimMobiles);

        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $paginatedData = array_slice($summaryData, $offset, $perPage);
        $total = count($summaryData);
        $lastPage = ceil($total / $perPage);

        return response()->json([
            'data' => $paginatedData,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => max($lastPage, 1),
        ]);
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $authUser = auth()->user();
        $accessibleSimMobiles = $this->getAccessibleSimMobiles($authUser, $request);

        if (empty($accessibleSimMobiles)) {
            return response()->json([
                'message' => 'No data to export',
            ], 404);
        }

        $summaryData = $this->buildSummaryQuery($request, $accessibleSimMobiles);

        $format = $request->get('format');
        $timestamp = now()->format('Ymd_His');

        if ($format === 'csv') {
            $filename = "summary_report_{$timestamp}.csv";

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function () use ($summaryData) {
                $out = fopen('php://output', 'w');

                fputcsv($out, [
                    '#',
                    'Phone Number',
                    'Total Calls',
                    'Total Duration',
                    'Answered Calls',
                    'Answered Calls Duration',
                    // 'Answered Call Avg. Duration',
                    'Unique Clients',
                    'Inbound Total Call',
                    'Inbound Total Duration',
                    'Inbound Answered',
                    'Inbound Ans. Calls Duration',
                    // 'Inbound Ans. Calls Avg. Duration',
                    'Inbound Missed',
                    'Outbound Total Call',
                    'Outbound Total Duration',
                    'Outbound Answered',
                    'Outbound Ans. Calls Duration',
                    // 'Outbound Ans. Calls Avg. Duration',
                ]);

                foreach ($summaryData as $index => $row) {
                    fputcsv($out, [
                        $index + 1,
                        $row['phone_number'],
                        $row['total_calls'],
                        $row['total_duration_formatted'],
                        $row['answered_calls'],
                        $row['answered_duration_formatted'],
                        // $row['answered_avg_duration_formatted'],
                        $row['unique_clients'],
                        $row['inbound_total_calls'],
                        $row['inbound_total_duration_formatted'],
                        $row['inbound_answered'],
                        $row['inbound_answered_duration_formatted'],
                        // $row['inbound_answered_avg_duration_formatted'],
                        $row['inbound_missed'],
                        $row['outbound_total_calls'],
                        $row['outbound_total_duration_formatted'],
                        $row['outbound_answered'],
                        $row['outbound_answered_duration_formatted'],
                        // $row['outbound_answered_avg_duration_formatted'],
                    ]);
                }

                fclose($out);
            }, 200, $headers);
        }

        // Excel export
        $filename = "summary_report_{$timestamp}.xls";

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($summaryData) {
            echo "<table border=\"1\">";
            echo "<thead><tr>";
            foreach ([
                '#',
                'Phone Number',
                'Total Calls',
                'Total Duration',
                'Answered Calls',
                'Answered Calls Duration',
                // 'Answered Call Avg. Duration',
                'Unique Clients',
                'Inbound Total Call',
                'Inbound Total Duration',
                'Inbound Answered',
                'Inbound Ans. Calls Duration',
                // 'Inbound Ans. Calls Avg. Duration',
                'Inbound Missed',
                'Outbound Total Call',
                'Outbound Total Duration',
                'Outbound Answered',
                'Outbound Ans. Calls Duration',
                // 'Outbound Ans. Calls Avg. Duration',
            ] as $h) {
                echo '<th>' . htmlspecialchars($h, ENT_QUOTES, 'UTF-8') . '</th>';
            }
            echo "</tr></thead><tbody>";

            foreach ($summaryData as $index => $row) {
                echo '<tr>';
                echo '<td>' . ($index + 1) . '</td>';
                echo '<td>' . htmlspecialchars($row['phone_number'], ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . $row['total_calls'] . '</td>';
                echo '<td>' . htmlspecialchars($row['total_duration_formatted'], ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . $row['answered_calls'] . '</td>';
                echo '<td>' . htmlspecialchars($row['answered_duration_formatted'], ENT_QUOTES, 'UTF-8') . '</td>';
                // echo '<td>' . htmlspecialchars($row['answered_avg_duration_formatted'], ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . $row['unique_clients'] . '</td>';
                echo '<td>' . $row['inbound_total_calls'] . '</td>';
                echo '<td>' . htmlspecialchars($row['inbound_total_duration_formatted'], ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . $row['inbound_answered'] . '</td>';
                echo '<td>' . htmlspecialchars($row['inbound_answered_duration_formatted'], ENT_QUOTES, 'UTF-8') . '</td>';
                // echo '<td>' . htmlspecialchars($row['inbound_answered_avg_duration_formatted'], ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . $row['inbound_missed'] . '</td>';
                echo '<td>' . $row['outbound_total_calls'] . '</td>';
                echo '<td>' . htmlspecialchars($row['outbound_total_duration_formatted'], ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . $row['outbound_answered'] . '</td>';
                echo '<td>' . htmlspecialchars($row['outbound_answered_duration_formatted'], ENT_QUOTES, 'UTF-8') . '</td>';
                // echo '<td>' . htmlspecialchars($row['outbound_answered_avg_duration_formatted'], ENT_QUOTES, 'UTF-8') . '</td>';
                echo '</tr>';
            }

            echo "</tbody></table>";
        }, 200, $headers);
    }

    private function buildSummaryQuery(Request $request, array $accessibleSimMobiles): array
    {
        $hasCallerIdColumn = Schema::hasColumn('call_logs', 'caller_id');

        if (empty($accessibleSimMobiles)) {
            return [];
        }

        // First, initialize all SIMs with zero data
        $summaryBySim = [];
        foreach ($accessibleSimMobiles as $simMobile) {
            $summaryBySim[$simMobile] = [
                'phone_number' => $simMobile,
                'total_calls' => 0,
                'total_duration_seconds' => 0,
                'answered_calls' => 0,
                'answered_duration_seconds' => 0,
                'unique_clients' => [],
                'inbound_total_calls' => 0,
                'inbound_total_duration_seconds' => 0,
                'inbound_answered' => 0,
                'inbound_answered_duration_seconds' => 0,
                'inbound_missed' => 0,
                'outbound_total_calls' => 0,
                'outbound_total_duration_seconds' => 0,
                'outbound_answered' => 0,
                'outbound_answered_duration_seconds' => 0,
                'outbound_missed' => 0,
            ];
        }

        // If caller_id column doesn't exist, return SIMs with zero data
        if (!$hasCallerIdColumn) {
            $summaryData = [];
            foreach ($summaryBySim as $callerId => $data) {
                $userInfo = $this->getUserInfoForSim($callerId);
                $summaryData[] = [
                    'phone_number' => $callerId . ($userInfo ? ' - ' . $userInfo : ''),
                    'total_calls' => 0,
                    'total_duration_formatted' => '0h 0m 0s',
                    'answered_calls' => 0,
                    'answered_duration_formatted' => '0h 0m 0s',
                    'answered_avg_duration_formatted' => '0h 0m 0s',
                    'unique_clients' => 0,
                    'inbound_total_calls' => 0,
                    'inbound_total_duration_formatted' => '0h 0m 0s',
                    'inbound_answered' => 0,
                    'inbound_answered_duration_formatted' => '0h 0m 0s',
                    'inbound_answered_avg_duration_formatted' => '0h 0m 0s',
                    'inbound_missed' => 0,
                    'outbound_total_calls' => 0,
                    'outbound_total_duration_formatted' => '0h 0m 0s',
                    'outbound_answered' => 0,
                    'outbound_answered_duration_formatted' => '0h 0m 0s',
                    'outbound_answered_avg_duration_formatted' => '0h 0m 0s',
                ];
            }
            return $summaryData;
        }

        // Now get call log data
        $query = CallLog::query()
            ->select([
                'caller_id',
                'call_type',
                'call_status',
                'caller_number',
                DB::raw('COUNT(*) as call_count'),
                DB::raw('SUM(CASE WHEN caller_duration IS NOT NULL AND caller_duration != "" THEN TIME_TO_SEC(caller_duration) ELSE 0 END) as total_duration_seconds'),
            ])
            ->whereIn('caller_id', $accessibleSimMobiles)
            ->groupBy('caller_id', 'call_type', 'call_status', 'caller_number');

        // Apply filters
        if ($request->filled('start_date_time') && $request->filled('end_date_time')) {
            $start = Carbon::parse($request->start_date_time)->startOfDay();
            $end = Carbon::parse($request->end_date_time)->endOfDay();
            $query->whereBetween('date_time', [$start, $end]);
        } elseif ($request->filled('start_date_time')) {
            $start = Carbon::parse($request->start_date_time)->startOfDay();
            $query->where('date_time', '>=', $start);
        } elseif ($request->filled('end_date_time')) {
            $end = Carbon::parse($request->end_date_time)->endOfDay();
            $query->where('date_time', '<=', $end);
        }

        if ($request->filled('call_type')) {
            $query->where('call_type', $request->call_type);
        }

        if ($request->filled('call_status')) {
            $query->where('call_status', $request->call_status);
        }

        $results = $query->get();

        // Process the results to update summary data

        foreach ($results as $result) {
            $callerId = $result->caller_id;
            $callType = $result->call_type;
            $callStatus = $result->call_status;
            $callerNumber = $result->caller_number;
            $callCount = (int) $result->call_count;
            $durationSeconds = (int) $result->total_duration_seconds;

            // Skip if this SIM is not in our accessible list (shouldn't happen but safety check)
            if (!isset($summaryBySim[$callerId])) {
                continue;
            }

            // Update the existing SIM entry (already initialized with zeros)
            $summaryBySim[$callerId]['total_calls'] += $callCount;
            $summaryBySim[$callerId]['total_duration_seconds'] += $durationSeconds;

            if ($callStatus === 'Answered') {
                $summaryBySim[$callerId]['answered_calls'] += $callCount;
                $summaryBySim[$callerId]['answered_duration_seconds'] += $durationSeconds;
            }

            // Track unique clients
            if ($callerNumber && !in_array($callerNumber, $summaryBySim[$callerId]['unique_clients'])) {
                $summaryBySim[$callerId]['unique_clients'][] = $callerNumber;
            }

            if ($callType === 'inbound') {
                $summaryBySim[$callerId]['inbound_total_calls'] += $callCount;
                $summaryBySim[$callerId]['inbound_total_duration_seconds'] += $durationSeconds;

                if ($callStatus === 'Answered') {
                    $summaryBySim[$callerId]['inbound_answered'] += $callCount;
                    $summaryBySim[$callerId]['inbound_answered_duration_seconds'] += $durationSeconds;
                } elseif ($callStatus === 'Missed') {
                    $summaryBySim[$callerId]['inbound_missed'] += $callCount;
                }
            } elseif ($callType === 'outbound') {
                $summaryBySim[$callerId]['outbound_total_calls'] += $callCount;
                $summaryBySim[$callerId]['outbound_total_duration_seconds'] += $durationSeconds;

                if ($callStatus === 'Answered') {
                    $summaryBySim[$callerId]['outbound_answered'] += $callCount;
                    $summaryBySim[$callerId]['outbound_answered_duration_seconds'] += $durationSeconds;
                } elseif ($callStatus === 'Missed') {
                    $summaryBySim[$callerId]['outbound_missed'] += $callCount;
                }
            }
        }

        // Format the data and get user names
        $summaryData = [];
        foreach ($summaryBySim as $callerId => $data) {
            // Get user info for this SIM
            $userInfo = $this->getUserInfoForSim($callerId);

            $summaryData[] = [
                'phone_number' => $callerId . ($userInfo ? ' - ' . $userInfo : ''),
                'total_calls' => $data['total_calls'],
                'total_duration_formatted' => $this->formatDuration($data['total_duration_seconds']),
                'answered_calls' => $data['answered_calls'],
                'answered_duration_formatted' => $this->formatDuration($data['answered_duration_seconds']),
                'answered_avg_duration_formatted' => $data['answered_calls'] > 0 
                    ? $this->formatDuration((int)($data['answered_duration_seconds'] / $data['answered_calls'])) 
                    : '0h 0m 0s',
                'unique_clients' => count($data['unique_clients']),
                'inbound_total_calls' => $data['inbound_total_calls'],
                'inbound_total_duration_formatted' => $this->formatDuration($data['inbound_total_duration_seconds']),
                'inbound_answered' => $data['inbound_answered'],
                'inbound_answered_duration_formatted' => $this->formatDuration($data['inbound_answered_duration_seconds']),
                'inbound_answered_avg_duration_formatted' => $data['inbound_answered'] > 0 
                    ? $this->formatDuration((int)($data['inbound_answered_duration_seconds'] / $data['inbound_answered'])) 
                    : '0h 0m 0s',
                'inbound_missed' => $data['inbound_missed'],
                'outbound_total_calls' => $data['outbound_total_calls'],
                'outbound_total_duration_formatted' => $this->formatDuration($data['outbound_total_duration_seconds']),
                'outbound_answered' => $data['outbound_answered'],
                'outbound_answered_duration_formatted' => $this->formatDuration($data['outbound_answered_duration_seconds']),
                'outbound_answered_avg_duration_formatted' => $data['outbound_answered'] > 0 
                    ? $this->formatDuration((int)($data['outbound_answered_duration_seconds'] / $data['outbound_answered'])) 
                    : '0h 0m 0s',
            ];
        }

        return $summaryData;
    }

    private function getUserInfoForSim(string $mobile): ?string
    {
        $sim = Sim::query()
            ->leftJoin('departments', 'departments.id', '=', 'sims.department_id')
            ->where('sims.mobile', $mobile)
            ->select(['sims.name', 'departments.name as department_name'])
            ->first();

        if ($sim && $sim->name) {
            return $sim->name . ($sim->department_name ? ' - ' . $sim->department_name : '');
        }

        return null;
    }

    private function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return "{$hours}h {$minutes}m {$secs}s";
    }

    private function getAccessibleSimMobiles($authUser, Request $request): array
    {
        $role = $authUser->role;
        $hasCallerIdColumn = Schema::hasColumn('call_logs', 'caller_id');

        if (!$hasCallerIdColumn) {
            return [];
        }

        $simMobiles = [];

        if ($role === 'user') {
            $simMobiles = $this->getUserSimMobilesForCallerId($authUser->id);
        } elseif ($role === 'admin') {
            // Admin can see all or filtered by organization
            if ($request->filled('organization_id')) {
                $orgId = (int) $request->organization_id;
                
                if ($request->filled('user_id')) {
                    $simMobiles = $this->getUserSimMobilesForCallerId((int) $request->user_id);
                } elseif ($request->filled('department_id')) {
                    $simMobiles = Sim::where('organization_id', $orgId)
                        ->where('department_id', (int) $request->department_id)
                        ->pluck('mobile')
                        ->filter()
                        ->unique()
                        ->toArray();
                } else {
                    $simMobiles = Sim::where('organization_id', $orgId)
                        ->pluck('mobile')
                        ->filter()
                        ->unique()
                        ->toArray();
                }
            } else {
                // All SIMs for admin if no org selected
                $simMobiles = Sim::pluck('mobile')
                    ->filter()
                    ->unique()
                    ->toArray();
            }
        } elseif ($role === 'manager') {
            $accessible = $this->getManagerAccessibleDepartmentIds($authUser);
            
            if (!empty($accessible)) {
                if ($request->filled('user_id')) {
                    $selectedUserId = (int) $request->user_id;
                    $selectedUser = User::find($selectedUserId);
                    
                    if ($selectedUser && $selectedUser->role === 'user' && in_array((int) $selectedUser->department_id, $accessible, true)) {
                        $simMobiles = $this->getUserSimMobilesForCallerId($selectedUserId);
                    }
                } elseif ($request->filled('department_id')) {
                    // Filter by specific department
                    $deptId = (int) $request->department_id;
                    if (in_array($deptId, $accessible, true)) {
                        $simMobiles = Sim::where('department_id', $deptId)
                            ->pluck('mobile')
                            ->filter()
                            ->unique()
                            ->toArray();
                    }
                } else {
                    // Get all SIMs from accessible departments
                    $simMobiles = Sim::whereIn('department_id', $accessible)
                        ->pluck('mobile')
                        ->filter()
                        ->unique()
                        ->toArray();
                }
            }
        } else {
            // organization role
            $orgId = $authUser->organization_id;
            
            if ($request->filled('user_id')) {
                $selectedUserId = (int) $request->user_id;
                $selectedUser = User::find($selectedUserId);
                
                if ($selectedUser && (int) $selectedUser->organization_id === (int) $orgId) {
                    $simMobiles = $this->getUserSimMobilesForCallerId($selectedUserId);
                }
            } elseif ($request->filled('department_id')) {
                $simMobiles = Sim::where('organization_id', $orgId)
                    ->where('department_id', (int) $request->department_id)
                    ->pluck('mobile')
                    ->filter()
                    ->unique()
                    ->toArray();
            } else {
                $simMobiles = Sim::where('organization_id', $orgId)
                    ->pluck('mobile')
                    ->filter()
                    ->unique()
                    ->toArray();
            }
        }

        // Apply SIM filter if provided
        if ($request->filled('sim_mobile') && is_array($request->sim_mobile)) {
            $requestedSims = array_filter(array_map('trim', $request->sim_mobile));
            $simMobiles = array_intersect($simMobiles, $requestedSims);
        }

        return array_values($simMobiles);
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
