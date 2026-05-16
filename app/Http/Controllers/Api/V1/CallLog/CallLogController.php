<?php

namespace App\Http\Controllers\Api\V1\CallLog;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\CallLog;
use App\Models\OrganizationSetting;
use App\Services\ExclusionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CallLogController extends Controller
{
    use ApiResponse;

    private const MAX_PER_PAGE = 100;

    /**
     * Push call log from mobile app.
     * The entire operation — log creation + callback update — is wrapped in
     * a DB transaction so we never have partial state.
     */
    public function push(Request $request)
    {
        $request->validate([
            'unique_id'             => 'required|string|max:100|unique:call_logs,unique_id',
            'date_time'             => 'required|date',
            'call_status'           => 'required|string|max:50',
            'caller_number'         => 'required|string|max:20',
            'call_type'             => 'required|string|max:20',
            'caller_duration'       => 'nullable|numeric|min:0|max:86400',
            'conversation_duration' => 'nullable|numeric|min:0|max:86400',
            'ring_duration'         => 'nullable|numeric|min:0|max:86400',
            'contact_status'        => 'nullable|string|max:50',
            'name'                  => 'nullable|string|max:255',
            'hangup_by'             => 'nullable|string|max:50',
        ]);

        $sim = $request->user();
        $sim->load(['organization', 'team']);

        $dateTime             = Carbon::parse($request->date_time);
        $callerDuration       = $request->caller_duration       ? gmdate('H:i:s', (int) $request->caller_duration)       : '00:00:00';
        $conversationDuration = $request->conversation_duration ? gmdate('H:i:s', (int) $request->conversation_duration) : '00:00:00';
        $ringDuration         = $request->ring_duration         ? gmdate('H:i:s', (int) $request->ring_duration)         : '00:00:00';

        $callLog = DB::transaction(function () use (
            $request, $sim, $dateTime, $callerDuration, $conversationDuration, $ringDuration
        ) {
            $log = CallLog::create([
                'unique_id'             => $request->unique_id,
                'organization_id'       => $sim->organization_id,
                'caller_id'             => $sim->mobile,
                'date'                  => $dateTime->format('Y-m-d'),
                'time'                  => $dateTime->format('H:i:s'),
                'date_time'             => $request->date_time,
                'call_type'             => $request->call_type,
                'call_status'           => $request->call_status,
                'caller_number'         => $request->caller_number,
                'caller_duration'       => $callerDuration,
                'conversation_duration' => $conversationDuration,
                'ring_duration'         => $ringDuration,
                'contact_status'        => $request->contact_status,
                'contact_name'          => $request->name,
                'hangup_by'             => $request->hangup_by,
                'name'                  => $sim->name,
                'team_name'             => $sim->team ? $sim->team->name : null,
            ]);

            // Update callback flags for previous missed calls — inside the same transaction
            if ($request->call_status === 'Answered') {
                $this->processCallbackLogic($log, $sim->organization_id);
            }

            return $log;
        });

        return $this->successResponse($callLog, 'Call log saved successfully', 201);
    }

    /**
     * Mark previous missed calls from the same number as returned.
     * Runs inside the push() transaction — any exception rolls back both the
     * call log creation and these updates.
     */
    private function processCallbackLogic(CallLog $currentCall, int $organizationId): void
    {
        $orgSettings = OrganizationSetting::where('organization_id', $organizationId)
            ->select('callback_window_hours')
            ->first();

        $callbackWindowHours = (int) ($orgSettings->callback_window_hours ?? 48);
        $callbackWindowDays  = $callbackWindowHours / 24;

        $callbackStartDate = Carbon::parse($currentCall->date_time)->subDays($callbackWindowDays);

        CallLog::where('caller_id', $currentCall->caller_id)
            ->where('caller_number', $currentCall->caller_number)
            ->where('call_status', 'Missed')
            ->whereNull('callback_id')
            ->whereBetween('date_time', [$callbackStartDate, $currentCall->date_time])
            ->update([
                'call_back'   => 'Y',
                'callback_id' => $currentCall->unique_id,
            ]);
    }

    /**
     * List call logs for the authenticated SIM — paginated.
     */
    public function list(Request $request)
    {
        $request->validate([
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'filter_type' => 'nullable|string|in:all,inbound,outbound,missed',
            'page'        => 'nullable|integer|min:1',
            'per_page'    => 'nullable|integer|min:1|max:' . self::MAX_PER_PAGE,
        ]);

        $sim      = $request->user();
        $filter   = $request->filter_type ?? 'all';
        $perPage  = min((int) ($request->per_page ?? 30), self::MAX_PER_PAGE);

        $query = CallLog::select([
                'id', 'unique_id', 'time', 'date_time',
                'call_type', 'call_status', 'caller_number',
                'caller_duration', 'contact_name',
            ])
            ->where('caller_id', $sim->mobile)
            ->whereBetween('date', [$request->start_date, $request->end_date]);

        match ($filter) {
            'inbound'  => $query->where('call_type', 'inbound'),
            'outbound' => $query->where('call_type', 'outbound'),
            'missed'   => $query->where('call_status', 'Missed'),
            default    => null,
        };

        $query->orderBy('date_time', 'desc');

        ExclusionService::applyToQuery($query, (int) $sim->organization_id);

        $logs = $query->paginate($perPage);

        return $this->successResponse($logs, 'Call logs fetched successfully');
    }
}
