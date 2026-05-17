<?php

namespace App\Mail;

use App\Models\OrganizationSubscription;
use App\Services\InvoiceService;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SubscriptionPurchaseMail extends Mailable
{
    public string $invoiceNumber;

    public function __construct(
        public OrganizationSubscription $subscription,
        public string $emailType = 'renewal',
    ) {
        $this->invoiceNumber = InvoiceService::ensureSubscriptionInvoice($subscription);
    }

    public function envelope(): Envelope
    {
        $subject = $this->emailType === 'auto_renewal'
            ? "Auto-Renewal Successful – {$this->invoiceNumber}"
            : "Subscription Confirmed – {$this->invoiceNumber}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.subscription-purchase');
    }

    public function attachments(): array
    {
        $invoiceNumber = $this->invoiceNumber;
        $filename = strtolower($invoiceNumber) . '.pdf';

        return [
            Attachment::fromData(
                fn () => InvoiceService::generateSubscriptionPdf($this->subscription, $invoiceNumber),
                $filename
            )->withMime('application/pdf'),
        ];
    }
}
