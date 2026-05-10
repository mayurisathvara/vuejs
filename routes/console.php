<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('subscriptions:sync-lifecycle', function () {
    $result = app(\App\Services\RazorpaySubscriptionService::class)->syncLifecycle();

    $this->info("Expired {$result['expired']} subscription(s); activated {$result['activated']} upcoming subscription(s).");
})->purpose('Expire old subscriptions and activate due upcoming subscriptions');

Schedule::command('subscriptions:sync-lifecycle')->dailyAt('00:10');
