<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $resetUrl;

    public function __construct(
        public string $email,
        public string $token
    ) {
        $appUrl = rtrim(config('app.url'), '/');
        $this->resetUrl = "{$appUrl}/reset-password?token={$token}&email=" . urlencode($email);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Reset Your Password — Callytics');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.reset-password');
    }
}
