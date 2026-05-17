<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ---------------------------------------------------------------------------
// Subscription lifecycle — expire stale active rows, activate upcoming ones
// ---------------------------------------------------------------------------
Artisan::command('subscriptions:sync-lifecycle', function () {
    $result = app(\App\Services\RazorpaySubscriptionService::class)->syncLifecycle();

    $this->info("Expired {$result['expired']} subscription(s); activated {$result['activated']} upcoming subscription(s).");
})->purpose('Expire old subscriptions and activate due upcoming subscriptions');

// ---------------------------------------------------------------------------
// Auto-renewal reminders — notify organizations 3 days before expiry
// ---------------------------------------------------------------------------
Artisan::command('subscriptions:send-reminders', function () {
    $cutoff = \Carbon\Carbon::today()->addDays(3)->toDateString();

    $expiring = \App\Models\OrganizationSubscription::with('organization:id,name,email')
        ->where('status', 'active')
        ->whereDate('end_date', $cutoff)
        ->get();

    $sent = 0;

    foreach ($expiring as $subscription) {
        $org = $subscription->organization;

        if (! $org?->email) {
            continue;
        }

        \Illuminate\Support\Facades\Log::info('Subscription expiry reminder', [
            'organization_id'  => $org->id,
            'organization'     => $org->name,
            'end_date'         => $subscription->end_date?->toDateString(),
            'auto_renew'       => $subscription->auto_renew,
        ]);

        // TODO: swap Log::info() for a real Mailable/notification once email is configured.

        $sent++;
    }

    $this->info("Sent {$sent} expiry reminder(s) for subscriptions ending on {$cutoff}.");
})->purpose('Notify organizations whose subscription expires in 3 days');

// ---------------------------------------------------------------------------
// Auto-renewal failure sweep — disable auto_renew for subscriptions that have
// been expired for more than 7 days without a successful charge.
// ---------------------------------------------------------------------------
Artisan::command('subscriptions:cleanup-failed-renewals', function () {
    $threshold = \Carbon\Carbon::today()->subDays(7)->toDateString();

    $updated = \App\Models\OrganizationSubscription::where('status', 'expired')
        ->where('auto_renew', true)
        ->whereDate('end_date', '<', $threshold)
        ->update([
            'auto_renew'                => false,
            'auto_renew_failure_reason' => 'Auto-renewal disabled after 7 days without a successful charge.',
        ]);

    $this->info("Cleaned up {$updated} stale auto-renewal flag(s).");
})->purpose('Disable auto_renew on subscriptions expired for more than 7 days');

// ---------------------------------------------------------------------------
// Schedule
// ---------------------------------------------------------------------------

// 00:10 — expire stale active subscriptions and activate any upcoming ones
Schedule::command('subscriptions:sync-lifecycle')->dailyAt('00:10');

// 09:00 — send 3-day expiry reminders to organizations
Schedule::command('subscriptions:send-reminders')->dailyAt('09:00');

// 02:00 — clean up stale auto-renew flags on long-expired subscriptions
Schedule::command('subscriptions:cleanup-failed-renewals')->dailyAt('02:00');

// ---------------------------------------------------------------------------
// Generated report cleanup — delete expired report files and records
// ---------------------------------------------------------------------------

Artisan::command('reports:cleanup', function () {
    $retentionDays = (int) config('reports.storage_days', 2);

    $expired = \App\Models\GeneratedReport::where('expires_at', '<', now())
        ->orWhere(function ($q) {
            // Also clean up failed/stuck records older than 7 days
            $q->whereIn('status', ['pending', 'generating', 'failed'])
              ->where('created_at', '<', now()->subDays(7));
        })
        ->get();

    $deleted = 0;
    foreach ($expired as $report) {
        if ($report->file_path) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($report->file_path);
        }
        $report->delete();
        $deleted++;
    }

    $this->info("Cleaned up {$deleted} expired report(s). Retention: {$retentionDays} day(s).");
})->purpose("Delete expired generated report files and records");

// 03:00 — clean up expired generated reports daily
Schedule::command('reports:cleanup')->dailyAt('03:00');
