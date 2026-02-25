<?php

namespace App\Http\Controllers\Api\V1\CallLog;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\CallLog;
use App\Models\OrganizationSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CallLogController extends Controller
{
    use ApiResponse;
    /**
     * Push call log from mobile app.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function push(Request $request)
    {
        $request->validate([
            'unique_id' => 'required|string|unique:call_logs,unique_id',
            'date_time' => 'required|date',
            'call_status' => 'required|string',
            'caller_number' => 'required|string',
            'call_type' => 'required|string',
            'caller_duration' => 'nullable|string',
            'conversation_duration' => 'nullable|string',
            'ring_duration' => 'nullable|string',
            'contact_status' => 'nullable|string',
            'name' => 'nullable|string',
            'hangup_by' => 'nullable|string',
        ]);

        $sim = $request->user();
        $sim->load(['organization', 'department']);

        // Extract date and time from date_time
        $dateTime = \Carbon\Carbon::parse($request->date_time);

        // Convert duration from seconds to time format (HH:MM:SS)
        $callerDuration = $request->caller_duration ? gmdate("H:i:s", $request->caller_duration) : '00:00:00';
        $conversationDuration = $request->conversation_duration ? gmdate("H:i:s", $request->conversation_duration) : '00:00:00';
        $ringDuration = $request->ring_duration ? gmdate("H:i:s", $request->ring_duration) : '00:00:00';

        // Create call log
        $callLog = CallLog::create([
            'unique_id' => $request->unique_id,
            'organization_id' => $sim->organization_id,
            'caller_id' => $sim->mobile,
            'date' => $dateTime->format('Y-m-d'),
            'time' => $dateTime->format('H:i:s'),
            'date_time' => $request->date_time,
            'call_type' => $request->call_type,
            'call_status' => $request->call_status,
            'caller_number' => $request->caller_number,
            'caller_duration' => $callerDuration,
            'conversation_duration' => $conversationDuration,
            'ring_duration' => $ringDuration,
            'contact_status' => $request->contact_status,
            'contact_name' => $request->name,
            'hangup_by' => $request->hangup_by,
            'name' => $sim->name,
            'department_name' => $sim->department ? $sim->department->name : null,
        ]);

        // Process callback logic if current call is answered
        if ($request->call_status === 'Answered') {
            $this->processCallbackLogic($callLog, $sim->organization_id);
        }

        return $this->successResponse($callLog, 'Call log saved successfully', 201);
    }

    /**
     * Process callback logic to mark previous missed calls.
     *
     * @param  \App\Models\CallLog  $currentCall
     * @param  int  $organizationId
     * @return void
     */
    private function processCallbackLogic(CallLog $currentCall, int $organizationId)
    {
        try {
            // Get callback window hours from organization settings
            $orgSettings = OrganizationSetting::where('organization_id', $organizationId)
                ->select('callback_window_hours')
                ->first();
            
            $callbackWindowHours = $orgSettings->callback_window_hours ?? 48;

            // Convert hours to days for date calculation
            $callbackWindowDays = $callbackWindowHours / 24;

            // Calculate the start date for callback window
            $callbackStartDate = Carbon::parse($currentCall->date_time)
                ->subDays($callbackWindowDays);

            // Update all previous missed calls in a single optimized query
            // Uses composite indexes: (caller_id, caller_number, call_status, callback_id, date_time)
            CallLog::where('caller_id', $currentCall->caller_id)
                ->where('caller_number', $currentCall->caller_number)
                ->where('call_status', 'Missed')
                ->whereNull('callback_id')
                ->whereBetween('date_time', [$callbackStartDate, $currentCall->date_time])
                ->update([
                    'call_back' => 'Y',
                    'callback_id' => $currentCall->unique_id,
                ]);
        } catch (\Exception $e) {
            // Log the error but don't fail the call log creation
            \Log::error('Callback logic failed: ' . $e->getMessage(), [
                'current_call_id' => $currentCall->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
	
	public function list(Request $request)
	{
		$request->validate([
			'start_date'  => 'required|date',
			'end_date'    => 'required|date',
			'filter_type' => 'nullable|string|in:all,inbound,outbound,missed',
			'page'        => 'nullable|integer|min:1'
		]);

        $sim = $request->user();
        $simMobile = $sim->mobile;
		$filter = $request->filter_type ?? 'all';

		// Base query — selecting only required fields
		$query = CallLog::select([
				'id',
				'unique_id',
				'time',
				'date_time',
				'call_type',
				'call_status',
				'caller_number',
				'caller_duration',
				'contact_name'
			])
            ->where('caller_id', $simMobile)
			->whereBetween('date', [$request->start_date, $request->end_date]);

		// Filters
		if ($filter === 'inbound') {
			$query->where('call_type', 'inbound');
		} elseif ($filter === 'outbound') {
			$query->where('call_type', 'outbound');
		} elseif ($filter === 'missed') {
			$query->where('call_status', 'Missed');
		}

		// Order latest first
		$query->orderBy('date_time', 'desc');

		// Pagination (30 per page)
		$logs = $query->paginate(30);

		return $this->successResponse($logs, 'Call logs fetched successfully');
	}

}
