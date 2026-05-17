<?php

namespace App\Jobs;

use App\Models\CallLog;
use App\Models\GeneratedReport;
use App\Models\Sim;
use App\Models\User;
use App\Models\UserSim;
use App\Notifications\ReportReadyNotification;
use App\Services\ExclusionService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class GenerateSummaryReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300;

    public function __construct(
        public readonly GeneratedReport $report,
        public readonly User $user,
    ) {}

    public function handle(): void
    {
        $this->report->update(['status' => 'generating']);

        try {
            $accessibleSimMobiles = $this->getAccessibleSimMobiles($this->report->filters ?? []);
            $summaryData          = $this->buildSummaryData($accessibleSimMobiles, $this->report->filters ?? []);
            $content              = $this->report->format === 'excel'
                ? $this->buildExcel($summaryData)
                : $this->buildCsv($summaryData);

            $this->storeFile($content);
            $this->user->notify(new ReportReadyNotification($this->report->fresh()));
        } catch (\Throwable $e) {
            $this->report->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    // -------------------------------------------------------------------------

    private function buildCsv(array $summaryData): string
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, [
            '#', 'Phone Number', 'Name', 'Team',
            'Total Calls', 'Total Unique Calls', 'Total Duration',
            'Answered Calls', 'Answered Calls Duration', 'Answered Call Avg. Duration',
            'Inbound Total Call', 'Inbound Total Duration', 'Inbound Answered',
            'Inbound Ans. Calls Duration', 'Inbound Ans. Calls Avg. Duration', 'Inbound Missed',
            'Outbound Total Call', 'Outbound Total Duration', 'Outbound Answered',
            'Outbound Ans. Calls Duration', 'Outbound Ans. Calls Avg. Duration',
        ]);

        foreach ($summaryData as $i => $row) {
            fputcsv($handle, [
                $i + 1,
                $row['phone_number'],
                $row['name'] ?? '-',
                $row['department'] ?? '-',
                $row['total_calls'],
                $row['unique_clients'],
                $row['total_duration_formatted'],
                $row['answered_calls'],
                $row['answered_duration_formatted'],
                $row['answered_avg_duration_formatted'],
                $row['inbound_total_calls'],
                $row['inbound_total_duration_formatted'],
                $row['inbound_answered'],
                $row['inbound_answered_duration_formatted'],
                $row['inbound_answered_avg_duration_formatted'],
                $row['inbound_missed'],
                $row['outbound_total_calls'],
                $row['outbound_total_duration_formatted'],
                $row['outbound_answered'],
                $row['outbound_answered_duration_formatted'],
                $row['outbound_answered_avg_duration_formatted'],
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }

    private function buildExcel(array $summaryData): string
    {
        $headers = [
            '#', 'Phone Number', 'Name', 'Team',
            'Total Calls', 'Total Unique Calls', 'Total Duration',
            'Answered Calls', 'Answered Calls Duration', 'Answered Call Avg. Duration',
            'Inbound Total Call', 'Inbound Total Duration', 'Inbound Answered',
            'Inbound Ans. Calls Duration', 'Inbound Ans. Calls Avg. Duration', 'Inbound Missed',
            'Outbound Total Call', 'Outbound Total Duration', 'Outbound Answered',
            'Outbound Ans. Calls Duration', 'Outbound Ans. Calls Avg. Duration',
        ];

        $html = '<table border="1"><thead><tr>';
        foreach ($headers as $h) {
            $html .= '<th>' . htmlspecialchars($h, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($summaryData as $i => $row) {
            $cells = [
                $i + 1,
                $row['phone_number'],
                $row['name'] ?? '-',
                $row['department'] ?? '-',
                $row['total_calls'],
                $row['unique_clients'],
                $row['total_duration_formatted'],
                $row['answered_calls'],
                $row['answered_duration_formatted'],
                $row['answered_avg_duration_formatted'],
                $row['inbound_total_calls'],
                $row['inbound_total_duration_formatted'],
                $row['inbound_answered'],
                $row['inbound_answered_duration_formatted'],
                $row['inbound_answered_avg_duration_formatted'],
                $row['inbound_missed'],
                $row['outbound_total_calls'],
                $row['outbound_total_duration_formatted'],
                $row['outbound_answered'],
                $row['outbound_answered_duration_formatted'],
                $row['outbound_answered_avg_duration_formatted'],
            ];
            $html .= '<tr>';
            foreach ($cells as $cell) {
                $html .= '<td>' . htmlspecialchars((string) ($cell ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            }
            $html .= '</tr>';
        }

        return $html . '</tbody></table>';
    }

    private function storeFile(string $content): void
    {
        $retentionDays = (int) config('reports.storage_days', 2);
        $ext           = $this->report->format === 'excel' ? 'xls' : 'csv';
        $timestamp     = now()->format('Ymd_His');
        $fileName      = "summary_report_{$timestamp}_{$this->report->id}.{$ext}";
        $path          = "reports/{$this->user->id}/{$fileName}";

        Storage::disk('local')->put($path, $content);

        $this->report->update([
            'status'       => 'ready',
            'file_path'    => $path,
            'file_name'    => $fileName,
            'file_size'    => strlen($content),
            'generated_at' => now(),
            'expires_at'   => now()->addDays($retentionDays),
        ]);
    }

    // -------------------------------------------------------------------------
    // Mirrors SummaryReportController::getAccessibleSimMobiles() + buildSummaryQuery()
    // -------------------------------------------------------------------------

    private function getAccessibleSimMobiles(array $filters): array
    {
        $role              = $this->user->role;
        $hasCallerIdColumn = Schema::hasColumn('call_logs', 'caller_id');

        if (!$hasCallerIdColumn) {
            return [];
        }

        $simMobiles = [];

        if ($role === 'user') {
            $simMobiles = $this->getUserSimMobiles($this->user->id);
        } elseif ($role === 'admin') {
            if (!empty($filters['organization_id'])) {
                $orgId = (int) $filters['organization_id'];
                if (!empty($filters['user_id'])) {
                    $simMobiles = $this->getUserSimMobiles((int) $filters['user_id']);
                } elseif (!empty($filters['team_id'])) {
                    $simMobiles = Sim::where('organization_id', $orgId)
                        ->where('team_id', (int) $filters['team_id'])
                        ->pluck('mobile')->filter()->unique()->toArray();
                } else {
                    $simMobiles = Sim::where('organization_id', $orgId)
                        ->pluck('mobile')->filter()->unique()->toArray();
                }
            } else {
                $simMobiles = Sim::pluck('mobile')->filter()->unique()->toArray();
            }
        } elseif ($role === 'manager') {
            $accessible = $this->getManagerTeamIds();
            if (!empty($accessible)) {
                if (!empty($filters['user_id'])) {
                    $selectedUser = User::find((int) $filters['user_id']);
                    if ($selectedUser && $selectedUser->role === 'user' && in_array((int) $selectedUser->team_id, $accessible, true)) {
                        $simMobiles = $this->getUserSimMobiles((int) $filters['user_id']);
                    }
                } elseif (!empty($filters['team_id'])) {
                    $teamId = (int) $filters['team_id'];
                    if (in_array($teamId, $accessible, true)) {
                        $simMobiles = Sim::where('team_id', $teamId)->pluck('mobile')->filter()->unique()->toArray();
                    }
                } else {
                    $simMobiles = Sim::whereIn('team_id', $accessible)->pluck('mobile')->filter()->unique()->toArray();
                }
            }
        } else {
            // organization role
            $orgId = $this->user->organization_id;
            if (!empty($filters['user_id'])) {
                $selectedUser = User::find((int) $filters['user_id']);
                if ($selectedUser && (int) $selectedUser->organization_id === (int) $orgId) {
                    $simMobiles = $this->getUserSimMobiles((int) $filters['user_id']);
                }
            } elseif (!empty($filters['team_id'])) {
                $simMobiles = Sim::where('organization_id', $orgId)
                    ->where('team_id', (int) $filters['team_id'])
                    ->pluck('mobile')->filter()->unique()->toArray();
            } else {
                $simMobiles = Sim::where('organization_id', $orgId)
                    ->pluck('mobile')->filter()->unique()->toArray();
            }
        }

        // Apply sim_mobile filter
        if (!empty($filters['sim_mobile']) && is_array($filters['sim_mobile'])) {
            $requested  = array_filter(array_map('trim', $filters['sim_mobile']));
            $simMobiles = array_intersect($simMobiles, $requested);
        }

        return array_values($simMobiles);
    }

    private function buildSummaryData(array $accessibleSimMobiles, array $filters): array
    {
        if (empty($accessibleSimMobiles)) {
            return [];
        }

        // Initialise per-SIM accumulators
        $summaryBySim = [];
        foreach ($accessibleSimMobiles as $mobile) {
            $summaryBySim[$mobile] = [
                'phone_number'                        => $mobile,
                'total_calls'                         => 0,
                'total_duration_seconds'              => 0,
                'answered_calls'                      => 0,
                'answered_duration_seconds'           => 0,
                'unique_clients'                      => [],
                'inbound_total_calls'                 => 0,
                'inbound_total_duration_seconds'      => 0,
                'inbound_answered'                    => 0,
                'inbound_answered_duration_seconds'   => 0,
                'inbound_missed'                      => 0,
                'outbound_total_calls'                => 0,
                'outbound_total_duration_seconds'     => 0,
                'outbound_answered'                   => 0,
                'outbound_answered_duration_seconds'  => 0,
                'outbound_missed'                     => 0,
            ];
        }

        $query = CallLog::query()
            ->select([
                'caller_id', 'call_type', 'call_status', 'caller_number',
                DB::raw('COUNT(*) as call_count'),
                DB::raw('SUM(CASE WHEN caller_duration IS NOT NULL AND caller_duration != "" THEN TIME_TO_SEC(caller_duration) ELSE 0 END) as total_duration_seconds'),
            ])
            ->whereIn('caller_id', $accessibleSimMobiles)
            ->groupBy('caller_id', 'call_type', 'call_status', 'caller_number');

        if (!empty($filters['start_date_time']) && !empty($filters['end_date_time'])) {
            $query->whereBetween('date_time', [
                Carbon::parse($filters['start_date_time'])->startOfDay(),
                Carbon::parse($filters['end_date_time'])->endOfDay(),
            ]);
        } elseif (!empty($filters['start_date_time'])) {
            $query->where('date_time', '>=', Carbon::parse($filters['start_date_time'])->startOfDay());
        } elseif (!empty($filters['end_date_time'])) {
            $query->where('date_time', '<=', Carbon::parse($filters['end_date_time'])->endOfDay());
        }

        if (!empty($filters['call_type'])) {
            $query->where('call_type', $filters['call_type']);
        }
        if (!empty($filters['call_status'])) {
            $query->where('call_status', $filters['call_status']);
        }

        ExclusionService::applyToQuery($query);

        foreach ($query->get() as $result) {
            $id     = $result->caller_id;
            $count  = (int) $result->call_count;
            $dur    = (int) $result->total_duration_seconds;
            $type   = $result->call_type;
            $status = $result->call_status;
            $number = $result->caller_number;

            if (!isset($summaryBySim[$id])) {
                continue;
            }

            $summaryBySim[$id]['total_calls']            += $count;
            $summaryBySim[$id]['total_duration_seconds'] += $dur;

            if ($status === 'Answered') {
                $summaryBySim[$id]['answered_calls']            += $count;
                $summaryBySim[$id]['answered_duration_seconds'] += $dur;
            }
            if ($number && !in_array($number, $summaryBySim[$id]['unique_clients'])) {
                $summaryBySim[$id]['unique_clients'][] = $number;
            }
            if ($type === 'inbound') {
                $summaryBySim[$id]['inbound_total_calls']            += $count;
                $summaryBySim[$id]['inbound_total_duration_seconds'] += $dur;
                if ($status === 'Answered') {
                    $summaryBySim[$id]['inbound_answered']                    += $count;
                    $summaryBySim[$id]['inbound_answered_duration_seconds']   += $dur;
                } elseif ($status === 'Missed') {
                    $summaryBySim[$id]['inbound_missed'] += $count;
                }
            } elseif ($type === 'outbound') {
                $summaryBySim[$id]['outbound_total_calls']            += $count;
                $summaryBySim[$id]['outbound_total_duration_seconds'] += $dur;
                if ($status === 'Answered') {
                    $summaryBySim[$id]['outbound_answered']                   += $count;
                    $summaryBySim[$id]['outbound_answered_duration_seconds']  += $dur;
                } elseif ($status === 'Missed') {
                    $summaryBySim[$id]['outbound_missed'] += $count;
                }
            }
        }

        $summaryData = [];
        foreach ($summaryBySim as $mobile => $data) {
            $userInfo      = $this->getUserInfoForSim($mobile);
            $summaryData[] = [
                'phone_number'                           => $mobile,
                'name'                                   => $userInfo['name'],
                'department'                             => $userInfo['department'],
                'total_calls'                            => $data['total_calls'],
                'total_duration_formatted'               => $this->formatDuration($data['total_duration_seconds']),
                'answered_calls'                         => $data['answered_calls'],
                'answered_duration_formatted'            => $this->formatDuration($data['answered_duration_seconds']),
                'answered_avg_duration_formatted'        => $data['answered_calls'] > 0
                    ? $this->formatDuration((int) ($data['answered_duration_seconds'] / $data['answered_calls']))
                    : '0h 0m 0s',
                'unique_clients'                         => count($data['unique_clients']),
                'inbound_total_calls'                    => $data['inbound_total_calls'],
                'inbound_total_duration_formatted'       => $this->formatDuration($data['inbound_total_duration_seconds']),
                'inbound_answered'                       => $data['inbound_answered'],
                'inbound_answered_duration_formatted'    => $this->formatDuration($data['inbound_answered_duration_seconds']),
                'inbound_answered_avg_duration_formatted'=> $data['inbound_answered'] > 0
                    ? $this->formatDuration((int) ($data['inbound_answered_duration_seconds'] / $data['inbound_answered']))
                    : '0h 0m 0s',
                'inbound_missed'                         => $data['inbound_missed'],
                'outbound_total_calls'                   => $data['outbound_total_calls'],
                'outbound_total_duration_formatted'      => $this->formatDuration($data['outbound_total_duration_seconds']),
                'outbound_answered'                      => $data['outbound_answered'],
                'outbound_answered_duration_formatted'   => $this->formatDuration($data['outbound_answered_duration_seconds']),
                'outbound_answered_avg_duration_formatted' => $data['outbound_answered'] > 0
                    ? $this->formatDuration((int) ($data['outbound_answered_duration_seconds'] / $data['outbound_answered']))
                    : '0h 0m 0s',
            ];
        }

        return $summaryData;
    }

    private function getUserInfoForSim(string $mobile): array
    {
        $sim = Sim::query()
            ->leftJoin('teams', 'teams.id', '=', 'sims.team_id')
            ->where('sims.mobile', $mobile)
            ->select(['sims.name', 'teams.name as team_name'])
            ->first();

        return [
            'name'       => $sim?->name,
            'department' => $sim?->team_name,
        ];
    }

    private function formatDuration(int $seconds): string
    {
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        return "{$h}h {$m}m {$s}s";
    }

    private function getUserSimMobiles(int $userId): array
    {
        return UserSim::query()
            ->leftJoin('sims', 'sims.id', '=', 'user_sims.sim_id')
            ->where('user_sims.user_id', $userId)
            ->selectRaw('COALESCE(sims.mobile, user_sims.mobile) as mobile')
            ->pluck('mobile')
            ->filter()
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '')
            ->unique()->values()->toArray();
    }

    private function getManagerTeamIds(): array
    {
        $ids = $this->user->team_id ? [$this->user->team_id] : [];
        $allowed = $this->user->allowed_team_ids;
        if ($allowed) {
            $allowed = is_string($allowed) ? json_decode($allowed, true) : $allowed;
            if (is_array($allowed)) {
                $ids = array_merge($ids, $allowed);
            }
        }
        return array_values(array_unique(array_filter($ids)));
    }
}
