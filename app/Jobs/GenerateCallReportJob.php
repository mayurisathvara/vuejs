<?php

namespace App\Jobs;

use App\Models\CallLog;
use App\Models\GeneratedReport;
use App\Models\Sim;
use App\Models\Team;
use App\Models\User;
use App\Models\UserSim;
use App\Notifications\ReportReadyNotification;
use App\Services\ExclusionService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class GenerateCallReportJob implements ShouldQueue
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
            $content = $this->generateContent();
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

    private function generateContent(): string
    {
        $query = $this->buildQuery($this->report->filters ?? []);

        return $this->report->format === 'excel'
            ? $this->buildExcel($query)
            : $this->buildCsv($query);
    }

    private function buildCsv(Builder $query): string
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, [
            'Date', 'Time', 'Caller Duration', 'Call Type',
            'Call Status', 'Team', 'User', 'SIM (caller_id)',
            'Customer Number', 'Return Call (Y/N)',
        ]);

        $query->lazy(1000)->each(function (CallLog $log) use ($handle) {
            fputcsv($handle, [
                optional($log->date)->format('Y-m-d') ?: optional($log->date_time)->format('Y-m-d'),
                $log->time ? (string) $log->time : optional($log->date_time)->format('H:i:s'),
                $log->caller_duration,
                $log->call_type,
                $log->call_status,
                $log->team_name,
                $log->name,
                $log->caller_id,
                $log->caller_number,
                $log->call_back,
            ]);
        });

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }

    private function buildExcel(Builder $query): string
    {
        $cols = ['Date','Time','Caller Duration','Call Type','Call Status','Team','User','SIM (caller_id)','Customer Number','Return Call (Y/N)'];
        $html = '<table border="1"><thead><tr>';
        foreach ($cols as $c) {
            $html .= '<th>' . htmlspecialchars($c, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        $query->lazy(1000)->each(function (CallLog $log) use (&$html) {
            $row = [
                optional($log->date)->format('Y-m-d') ?: optional($log->date_time)->format('Y-m-d'),
                $log->time ? (string) $log->time : optional($log->date_time)->format('H:i:s'),
                $log->caller_duration,
                $log->call_type,
                $log->call_status,
                $log->team_name,
                $log->name,
                $log->caller_id,
                $log->caller_number,
                $log->call_back,
            ];
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars((string) ($cell ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            }
            $html .= '</tr>';
        });

        return $html . '</tbody></table>';
    }

    private function storeFile(string $content): void
    {
        $retentionDays = (int) config('reports.storage_days', 2);
        $ext           = $this->report->format === 'excel' ? 'xls' : 'csv';
        $timestamp     = now()->format('Ymd_His');
        $fileName      = "call_report_{$timestamp}_{$this->report->id}.{$ext}";
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
    // Query builder — mirrors CallReportController::buildReportQuery() but
    // accepts a plain array of filters instead of a Request object.
    // -------------------------------------------------------------------------

    private function buildQuery(array $filters): Builder
    {
        $role              = $this->user->role;
        $hasCallerIdColumn = Schema::hasColumn('call_logs', 'caller_id');

        $select = [
            'call_logs.id', 'call_logs.organization_id', 'call_logs.name',
            'call_logs.date', 'call_logs.time', 'call_logs.date_time',
            'call_logs.call_type', 'call_logs.call_status',
            'call_logs.caller_number', 'call_logs.caller_duration',
            'call_logs.team_name', 'call_logs.call_back', 'call_logs.created_at',
        ];
        if ($hasCallerIdColumn) {
            $select[] = 'call_logs.caller_id';
        }

        $query = CallLog::query()->select($select);

        // Role-based scope
        if ($role === 'admin') {
            if (!empty($filters['organization_id'])) {
                $query->where('organization_id', (int) $filters['organization_id']);
            }
        } elseif ($role === 'user') {
            if (!$hasCallerIdColumn) {
                return $query->whereRaw('1 = 0');
            }
            $mobiles = $this->getUserSimMobiles($this->user->id);
            $mobiles ? $query->whereIn('call_logs.caller_id', $mobiles) : $query->whereRaw('1 = 0');
        } else {
            $query->where('organization_id', $this->user->organization_id);
        }

        if ($role === 'manager') {
            if (!$hasCallerIdColumn) {
                return $query->whereRaw('1 = 0');
            }
            $accessible = $this->getManagerTeamIds();
            if (!empty($accessible)) {
                $userIds = User::whereIn('team_id', $accessible)->where('role', 'user')->pluck('id')->toArray();
                if (!empty($userIds)) {
                    $mobiles = $this->getUserSimMobilesMany($userIds);
                    $mobiles ? $query->whereIn('call_logs.caller_id', $mobiles) : $query->whereRaw('1 = 0');
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Optional user_id filter
        if ($role !== 'user' && !empty($filters['user_id'])) {
            $selectedUserId = (int) $filters['user_id'];
            $selectedUser   = User::select(['id', 'organization_id', 'team_id', 'role'])->find($selectedUserId);
            $allowed        = false;

            if ($selectedUser) {
                if ($role === 'admin') {
                    $allowed = true;
                } elseif ($role === 'manager') {
                    $accessible = $this->getManagerTeamIds();
                    $allowed    = (int) $selectedUser->organization_id === (int) $this->user->organization_id
                        && $selectedUser->role === 'user'
                        && in_array((int) $selectedUser->team_id, $accessible, true);
                } else {
                    $allowed = (int) $selectedUser->organization_id === (int) $this->user->organization_id;
                }
            }

            if (!$hasCallerIdColumn || !$allowed) {
                return $query->whereRaw('1 = 0');
            }
            $mobiles = $this->getUserSimMobiles($selectedUserId);
            $mobiles ? $query->whereIn('call_logs.caller_id', $mobiles) : $query->whereRaw('1 = 0');
        }

        // Date range
        if (!empty($filters['start_date_time']) && !empty($filters['end_date_time'])) {
            $query->whereBetween('call_logs.date_time', [
                Carbon::parse($filters['start_date_time']),
                Carbon::parse($filters['end_date_time']),
            ]);
        } elseif (!empty($filters['start_date_time'])) {
            $query->where('call_logs.date_time', '>=', Carbon::parse($filters['start_date_time']));
        } elseif (!empty($filters['end_date_time'])) {
            $query->where('call_logs.date_time', '<=', Carbon::parse($filters['end_date_time']));
        }

        if (!empty($filters['call_type'])) {
            $query->where('call_logs.call_type', $filters['call_type']);
        }
        if (!empty($filters['call_status'])) {
            $query->where('call_logs.call_status', $filters['call_status']);
        }

        // Team filter
        if (!empty($filters['team_id'])) {
            $teamId         = (int) $filters['team_id'];
            $teamSimMobiles = Sim::where('team_id', $teamId)->pluck('mobile')->filter()->toArray();

            if ($hasCallerIdColumn && !empty($teamSimMobiles)) {
                $query->where(function (Builder $q) use ($teamSimMobiles, $teamId) {
                    $q->whereIn('call_logs.caller_id', $teamSimMobiles);
                    $team = Team::select(['name'])->find($teamId);
                    if ($team?->name) {
                        $q->orWhere('call_logs.team_name', $team->name);
                    }
                });
            } else {
                $team = Team::select(['name'])->find($teamId);
                $team?->name
                    ? $query->where('call_logs.team_name', $team->name)
                    : $query->whereRaw('1 = 0');
            }
        }

        // SIM filter
        if (!empty($filters['sim_mobile'])) {
            if ($hasCallerIdColumn) {
                $simMobiles = array_filter(array_map('trim', (array) $filters['sim_mobile']));
                if (!empty($simMobiles)) {
                    $query->whereIn('call_logs.caller_id', $simMobiles);
                }
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if (!empty($filters['caller_number'])) {
            $query->where('call_logs.caller_number', 'like', '%' . trim((string) $filters['caller_number']) . '%');
        }
        if (!empty($filters['call_back'])) {
            $query->where('call_logs.call_back', $filters['call_back']);
        }

        ExclusionService::applyToQuery($query);

        return $query->orderByDesc('call_logs.date_time');
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

    private function getUserSimMobilesMany(array $userIds): array
    {
        return UserSim::query()
            ->leftJoin('sims', 'sims.id', '=', 'user_sims.sim_id')
            ->whereIn('user_sims.user_id', $userIds)
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
