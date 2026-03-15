<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\CallLog;
use App\Services\ExclusionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * Optimized dashboard - single aggregated query per period (current + previous)
     */
    public function dashboard(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ]);

        $authSim = $request->user();
        $simMobile = $authSim->mobile;

        // Normalize input dates
        $start = Carbon::parse($request->start_date)->toDateString();
        $end   = Carbon::parse($request->end_date)->toDateString();

        // Period length (inclusive)
        $periodDays = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;

        // Previous period (same length immediately before start)
        $prevEnd = Carbon::parse($start)->subDay()->toDateString();
        $prevStart = Carbon::parse($start)->subDays($periodDays)->toDateString();

        // Aggregation SQL snippet (used for both current and previous period)
        $selectRaw = <<<SQL
            COUNT(*) AS total_calls,
            SUM(CASE WHEN call_status = 'Answered' THEN 1 ELSE 0 END) AS answered_calls,
            SUM(CASE WHEN call_status = 'Missed' THEN 1 ELSE 0 END) AS missed_calls,
            SUM(CASE WHEN call_status = 'No Answer' THEN 1 ELSE 0 END) AS no_answer_calls,
            SUM(CASE WHEN call_type = 'inbound' THEN 1 ELSE 0 END) AS inbound_total,
            SUM(CASE WHEN call_type = 'inbound' AND call_status = 'Answered' THEN 1 ELSE 0 END) AS inbound_answered,
            SUM(CASE WHEN call_type = 'outbound' THEN 1 ELSE 0 END) AS outbound_total,
            SUM(CASE WHEN call_type = 'outbound' AND call_status = 'Answered' THEN 1 ELSE 0 END) AS outbound_answered,
            AVG(NULLIF(TIME_TO_SEC(conversation_duration), 0)) AS avg_secs
        SQL;

        // CURRENT PERIOD AGGREGATES
        $currentQuery = CallLog::where('caller_id', $simMobile)
            ->whereBetween('date', [$start, $end]);
        ExclusionService::applyToQuery($currentQuery, (int) $authSim->organization_id);
        $current = $currentQuery->selectRaw($selectRaw)->first();

        // PREVIOUS PERIOD AGGREGATES
        $previousQuery = CallLog::where('caller_id', $simMobile)
            ->whereBetween('date', [$prevStart, $prevEnd]);
        ExclusionService::applyToQuery($previousQuery, (int) $authSim->organization_id);
        $previous = $previousQuery->selectRaw($selectRaw)->first();

        // Cast to integers / floats (safety)
        $totalCalls = (int) ($current->total_calls ?? 0);
        $answeredCalls = (int) ($current->answered_calls ?? 0);
        $missedCalls = (int) ($current->missed_calls ?? 0);
        $noAnswerCalls = (int) ($current->no_answer_calls ?? 0);
        $inboundTotal = (int) ($current->inbound_total ?? 0);
        $inboundAnswered = (int) ($current->inbound_answered ?? 0);
        $outboundTotal = (int) ($current->outbound_total ?? 0);
        $outboundAnswered = (int) ($current->outbound_answered ?? 0);
        $avgSec = (float) ($current->avg_secs ?? 0);

        $prevTotalCalls = (int) ($previous->total_calls ?? 0);
        $prevAnsweredCalls = (int) ($previous->answered_calls ?? 0);
        $prevMissedCalls = (int) ($previous->missed_calls ?? 0);
        $prevNoAnswerCalls = (int) ($previous->no_answer_calls ?? 0);
        $prevInboundTotal = (int) ($previous->inbound_total ?? 0);
        $prevInboundAnswered = (int) ($previous->inbound_answered ?? 0);
        $prevOutboundTotal = (int) ($previous->outbound_total ?? 0);
        $prevOutboundAnswered = (int) ($previous->outbound_answered ?? 0);
        $prevAvgSec = (float) ($previous->avg_secs ?? 0);

        // Derived / formatted values
        $avgDurationFormatted = $this->formatSecondsToReadable($avgSec);
        $prevAvgDurationFormatted = $this->formatSecondsToReadable($prevAvgSec);

        // Answer rates (%)
        $answerRate = $totalCalls > 0 ? ($answeredCalls / $totalCalls) * 100 : 0;
        $prevAnswerRate = $prevTotalCalls > 0 ? ($prevAnsweredCalls / $prevTotalCalls) * 100 : 0;

        // Percent changes
        $totalCallsChange = $this->percentChange($totalCalls, $prevTotalCalls);
        $answerRateChange = $this->percentChange($answerRate, $prevAnswerRate);
        $avgDurationChange = $this->percentChange($avgSec, $prevAvgSec);
        $inboundChange = $this->percentChange($inboundTotal, $prevInboundTotal);
        $outboundChange = $this->percentChange($outboundTotal, $prevOutboundTotal);
     
        // Compose response
        $data = [
            'summary' => [
                'total_calls' => [
                    'value' => $totalCalls,
                    'change' => $this->formatChangeLabel($totalCallsChange)
                ],
                'answer_rate' => [
                    'value' => round($answerRate) . '%',
                    'change' => $this->formatChangeLabel($answerRateChange)
                ],
                'avg_duration' => [
                    'value' => $avgDurationFormatted,
                    'change' => $this->formatChangeLabel($avgDurationChange)
                ],
            ],
            'outbound' => [
                'answered' => $outboundAnswered,
                'no_answer' => $noAnswerCalls,
                'total' => $outboundTotal,
                'change' => $this->formatChangeLabel($outboundChange)
            ],
            'inbound' => [
                'answered' => $inboundAnswered,
                'missed' => $missedCalls,
                'total' => $inboundTotal,
                'change' => $this->formatChangeLabel($inboundChange)
            ],
            'alerts' => [
                'missed_calls' => [
                    'value' => $missedCalls,
                ]
            ],
            'period' => [
                'start_date' => $start,
                'end_date' => $end,
                'previous_start' => $prevStart,
                'previous_end' => $prevEnd,
            ]
        ];

        return $this->successResponse($data, 'Dashboard data fetched');
    }

    /**
     * Format seconds to readable "Xm Ys"
     */
    private function formatSecondsToReadable($seconds)
    {
        $seconds = (int) round($seconds);
        if ($seconds <= 0) {
            return "0m 00s";
        }

        $m = floor($seconds / 60);
        $s = $seconds % 60;
        return sprintf("%dm %02ds", $m, $s);
    }

    /**
     * Percent change helper
     * Returns numeric percent (signed) e.g. 12 or -5
     *
     * Logic:
     * - if previous == 0:
     *    - if current == 0 -> 0
     *    - if current > 0  -> 100 (full increase)
     * - else: ((current - previous) / previous) * 100
     */
   private function percentChange($current, $previous)
	{
		$cur = (float) $current;
		$prev = (float) $previous;

		// If previous = 0
		if ($prev == 0.0) {
			if ($cur == 0.0) {
				return 0;       // no change
			}
			return 100;         // max increase
		}

		// Normal percentage change
		$change = (($cur - $prev) / $prev) * 100;

		// CLAMP result between -100% and +100%
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
    private function formatChangeLabel($percent)
    {
        if ($percent > 0) {
            return '+' . $percent . '%';
        } elseif ($percent < 0) {
            return (string) $percent . '%';
        }
        return '0%';
    }
}
