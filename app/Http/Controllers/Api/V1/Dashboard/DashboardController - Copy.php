<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CallLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ]);

        $userId = $request->user()->id;

        $start = $request->start_date;
        $end   = $request->end_date;

        // Base query
        $query = CallLog::where('user_id', $userId)
                        ->whereBetween('date', [$start, $end]);

        $totalCalls = (clone $query)->count();

        // Answered = inbound+outbound where call_status='Answered'
        $answeredCalls = (clone $query)
            ->where('call_status', 'Answered')
            ->count();

        $missedCalls = (clone $query)
            ->where('call_status', 'Missed')
            ->count();

        $noAnswerCalls = (clone $query)
            ->where('call_status', 'No Answer')
            ->count();

        // Inbound summary
        $inboundAnswered = (clone $query)
            ->where('call_type', 'inbound')
            ->where('call_status', 'Answered')
            ->count();

        $inboundMissed = (clone $query)
            ->where('call_type', 'inbound')
            ->where('call_status', 'Missed')
            ->count();

        $inboundTotal = $inboundAnswered + $inboundMissed;

        // Outbound summary
        $outboundAnswered = (clone $query)
            ->where('call_type', 'outbound')
            ->where('call_status', 'Answered')
            ->count();

        $outboundNoAnswer = (clone $query)
            ->where('call_type', 'outbound')
            ->where('call_status', 'No Answer')
            ->count();

        $outboundTotal = $outboundAnswered + $outboundNoAnswer;

        // Average Talk Duration
        $avgDuration = (clone $query)
            ->where('conversation_duration', '!=', '00:00:00')
            ->pluck('conversation_duration')
            ->map(function ($t) {
                return strtotime($t) - strtotime('00:00:00');
            })
            ->avg();

        $avgDurationFormatted = $avgDuration
            ? gmdate("i\m s\s", $avgDuration)
            : "0m 00s";

        // Answer rate formula OPTION C
        $answerRate = $totalCalls > 0 
            ? round(($answeredCalls / $totalCalls) * 100)
            : 0;

        return response()->json([
            'status' => true,
            'message' => "Dashboard data fetched",
            'data' => [
                'summary' => [
                    'total_calls' => $totalCalls,
                    'answer_rate' => $answerRate . '%',
                    'avg_duration' => $avgDurationFormatted,
                ],
                'outbound' => [
                    'answered' => $outboundAnswered,
                    'no_answer' => $outboundNoAnswer,
                    'total' => $outboundTotal,
                ],
                'inbound' => [
                    'answered' => $inboundAnswered,
                    'missed'   => $inboundMissed,
                    'total'    => $inboundTotal,
                ],
                'alerts' => [
                    'missed_calls' => $missedCalls,
                ]
            ]
        ]);
    }
}
