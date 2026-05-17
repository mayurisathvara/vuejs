<?php

namespace App\Jobs;

use App\Mail\SubscriptionPurchaseMail;
use App\Models\OrganizationSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionPurchaseEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;
    public int $timeout = 90;

    public function __construct(
        public OrganizationSubscription $subscription,
        public string $emailType = 'renewal',
    ) {}

    public function handle(): void
    {
        $subscription = $this->subscription->loadMissing('organization', 'plan');
        $email        = $subscription->organization?->email;

        if (! $email) {
            return;
        }

        Mail::to($email)->send(new SubscriptionPurchaseMail($subscription, $this->emailType));
    }
}
