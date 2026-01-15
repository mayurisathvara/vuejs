<?php

namespace App\Http\Controllers\Api\V1\Analytics;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\CallLog;

class AnalyticsController extends Controller
{
    use ApiResponse;

    /**
     * Call Type Distribution API
     */
   public function callTypeDistribution(Request $request)
	{
		$request->validate([
			'start_date' => 'required|date',
			'end_date'   => 'required|date',
		]);

		$user = $request->user();
		$simMobile = $user->mobile;

		// Base query
		$baseQuery = CallLog::where('caller_id', $simMobile)
			->whereBetween('date', [$request->start_date, $request->end_date]);

		// Incoming = all inbound calls (answered + missed)
		$incoming = (clone $baseQuery)
			->where('call_type', 'inbound')
			->count();

		// Outgoing = all outbound calls
		$outgoing = (clone $baseQuery)
			->where('call_type', 'outbound')
			->count();

		// Missed = inbound AND call_status = Missed
		$missed = (clone $baseQuery)
			->where('call_type', 'inbound')
			->where('call_status', 'Missed')
			->count();

		$total_calls = $incoming + $outgoing; // missed already included in inbound

		return $this->successResponse([
			'total_calls' => $total_calls,
			'incoming'    => $incoming,
			'outgoing'    => $outgoing,
			'missed'      => $missed,
		], 'Call type distribution fetched successfully');
	}
	
	public function dailyCallVolume(Request $request)
	{
		$user = $request->user();
		$simMobile = $user->mobile;

		// Last 7 days range
		$endDate = now()->toDateString();
		$startDate = now()->subDays(6)->toDateString();
		

		$records = CallLog::selectRaw('date, COUNT(*) as total')
		->where('caller_id', $simMobile)
		->whereBetween('date', [$startDate, $endDate])
		->groupBy('date')
		->pluck('total', 'date');  // <-- Key = date, Value = total

		$result = [];
		$totalCalls = 0;

		for ($i = 6; $i >= 0; $i--) {
			$date = now()->subDays($i)->toDateString();
			$count = $records[$date] ?? 0;  // <-- FIXED

			$result[] = [
				'date' => $date,
				'count' => $count
			];

			$totalCalls += $count;
		}

		$avg = $totalCalls > 0 ? round($totalCalls / 7) : 0;

		return $this->successResponse([
			'avg_per_day' => $avg,
			'days' => $result
		], 'Daily call volume fetched successfully');
	}
	
	public function peakHours(Request $request)
	{
		$request->validate([
			'start_date' => 'required|date',
			'end_date'   => 'required|date'
		]);

		$user = $request->user();
		$simMobile = $user->mobile;

		// STEP 1: Fetch hourly grouped data directly from MySQL (super fast)
		$hourly = CallLog::selectRaw('HOUR(time) as hour, COUNT(*) as total')
			->where('caller_id', $simMobile)
			->whereBetween('date', [$request->start_date, $request->end_date])
			->groupBy('hour')
			->pluck('total', 'hour'); // returns: [hour => count]

		if ($hourly->isEmpty()) {
			return $this->successResponse([
				'ranges' => []
			], 'Peak call hours fetched successfully');
		}

		// STEP 2: Define static 2-hour buckets
		$buckets = [
			'09-11' => 0,
			'11-13' => 0,
			'13-15' => 0,
			'15-17' => 0,
			'17-19' => 0,
			'19-21' => 0,
		];

		// STEP 3: Fill buckets using fast mapping
		foreach ($hourly as $hour => $count) {
			foreach ($buckets as $range => $total) {
				[$start, $end] = explode('-', $range);

				if ($hour >= (int)$start && $hour < (int)$end) {
					$buckets[$range] += $count;
					break;
				}
			}
		}

		// STEP 4: Sort by highest activity
		arsort($buckets);

		// STEP 5: Pick only TOP 2 buckets
		$topTwo = [];
		foreach (array_slice($buckets, 0, 2, true) as $range => $count) {
			$topTwo[] = [
				'range' => $this->formatRange($range),
				'calls' => $count
			];
		}

		return $this->successResponse([
			'ranges' => $topTwo
		], 'Peak call hours fetched successfully');
	}

	// Convert "09-11" to "9 AM – 11 AM"
	private function formatRange($range)
	{
		[$start, $end] = explode('-', $range);

		return date("g A", strtotime("$start:00")) . " – " . date("g A", strtotime("$end:00"));
	}
	
	public function missedCallsAnalysis(Request $request)
	{
		$request->validate([
			'start_date' => 'required|date',
			'end_date'   => 'required|date',
		]);

		$user = $request->user();

		
		return $this->successResponse([
			'total_missed' => 30,
			'returned_calls'    => 19,
			'callback_pending'    => 11,
		], 'Total Missed Calls fetched successfully');
	}



}
