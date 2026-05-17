<?php

namespace App\Mail;

use App\Models\AddonSimPurchase;
use App\Services\InvoiceService;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AddonSimPurchaseMail extends Mailable
{
    public string $invoiceNumber;

    public function __construct(public AddonSimPurchase $addon)
    {
        $this->invoiceNumber = InvoiceService::ensureAddonInvoice($addon);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Add-on SIMs Activated – {$this->invoiceNumber}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.addon-sim-purchase');
    }

    public function attachments(): array
    {
        $invoiceNumber = $this->invoiceNumber;
        $filename = strtolower($invoiceNumber) . '.pdf';

        return [
            Attachment::fromData(
                fn () => InvoiceService::generateAddonPdf($this->addon, $invoiceNumber),
                $filename
            )->withMime('application/pdf'),
        ];
    }
}
