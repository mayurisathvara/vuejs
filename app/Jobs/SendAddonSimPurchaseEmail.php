<?php

namespace App\Jobs;

use App\Mail\AddonSimPurchaseMail;
use App\Models\AddonSimPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAddonSimPurchaseEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;
    public int $timeout = 90;

    public function __construct(public AddonSimPurchase $addon) {}

    public function handle(): void
    {
        $addon = $this->addon->loadMissing('organization', 'subscription', 'plan');
        $email = $addon->organization?->email;

        if (! $email) {
            return;
        }

        Mail::to($email)->send(new AddonSimPurchaseMail($addon));
    }
}
