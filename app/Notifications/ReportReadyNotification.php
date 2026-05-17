<?php

namespace App\Notifications;

use App\Models\GeneratedReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReportReadyNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly GeneratedReport $report) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'report_id'    => $this->report->id,
            'report_type'  => $this->report->report_type,
            'report_label' => $this->report->report_label,
            'format'       => $this->report->format,
            'status'       => $this->report->status,
            'message'      => "Your {$this->report->report_label} is ready for download.",
            'url'          => '/generated-reports',
        ];
    }
}
