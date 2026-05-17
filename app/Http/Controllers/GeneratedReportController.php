<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateCallReportJob;
use App\Jobs\GenerateSummaryReportJob;
use App\Models\GeneratedReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GeneratedReportController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $reports = GeneratedReport::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        $reports->getCollection()->transform(function (GeneratedReport $r) {
            return [
                'id'            => $r->id,
                'report_type'   => $r->report_type,
                'report_label'  => $r->report_label,
                'format'        => $r->format,
                'format_label'  => $r->format_label,
                'status'        => $r->status,
                'file_name'     => $r->file_name,
                'file_size'     => $r->file_size,
                'filters'       => $r->filters,
                'generated_at'  => $r->generated_at?->toIso8601String(),
                'expires_at'    => $r->expires_at?->toIso8601String(),
                'error_message' => $r->error_message,
                'created_at'    => $r->created_at->toIso8601String(),
                'is_expired'    => $r->isExpired(),
            ];
        });

        return response()->json($reports);
    }

    public function queueCallReport(Request $request): JsonResponse
    {
        $request->validate([
            'format'  => ['required', 'in:csv,excel'],
            'filters' => ['nullable', 'array'],
        ]);

        $user    = auth()->user();
        $format  = $request->input('format', 'csv');
        $filters = $request->input('filters', []);

        $report = GeneratedReport::create([
            'user_id'         => $user->id,
            'organization_id' => $user->organization_id,
            'report_type'     => 'call_report',
            'format'          => $format,
            'status'          => 'pending',
            'file_name'       => 'call_report_pending.' . $this->ext($format),
            'filters'         => $filters,
        ]);

        GenerateCallReportJob::dispatch($report, $user);

        return response()->json([
            'message'   => "Your Call Report is being generated. You'll be notified when it's ready.",
            'report_id' => $report->id,
        ]);
    }

    public function queueSummaryReport(Request $request): JsonResponse
    {
        $request->validate([
            'format'  => ['required', 'in:csv,excel'],
            'filters' => ['nullable', 'array'],
        ]);

        $user    = auth()->user();
        $format  = $request->input('format', 'csv');
        $filters = $request->input('filters', []);

        $report = GeneratedReport::create([
            'user_id'         => $user->id,
            'organization_id' => $user->organization_id,
            'report_type'     => 'summary_report',
            'format'          => $format,
            'status'          => 'pending',
            'file_name'       => 'summary_report_pending.' . $this->ext($format),
            'filters'         => $filters,
        ]);

        GenerateSummaryReportJob::dispatch($report, $user);

        return response()->json([
            'message'   => "Your Summary Report is being generated. You'll be notified when it's ready.",
            'report_id' => $report->id,
        ]);
    }

    public function download(GeneratedReport $report): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth()->user();

        abort_unless((int) $report->user_id === (int) $user->id, 403, 'Forbidden.');
        abort_unless($report->status === 'ready', 422, 'Report is not ready yet.');

        if (!$report->file_path || !Storage::disk('local')->exists($report->file_path)) {
            abort(404, 'File not found. It may have expired.');
        }

        $ext      = pathinfo((string) $report->file_name, PATHINFO_EXTENSION);
        $mimeType = $ext === 'csv' ? 'text/csv' : 'application/vnd.ms-excel';

        return Storage::disk('local')->download(
            $report->file_path,
            $report->file_name,
            ['Content-Type' => $mimeType],
        );
    }

    public function destroy(GeneratedReport $report): JsonResponse
    {
        $user = auth()->user();

        abort_unless((int) $report->user_id === (int) $user->id, 403, 'Forbidden.');

        if ($report->file_path) {
            Storage::disk('local')->delete($report->file_path);
        }

        $report->delete();

        return response()->json(['message' => 'Report deleted.']);
    }

    private function ext(string $format): string
    {
        return $format === 'excel' ? 'xls' : 'csv';
    }
}
