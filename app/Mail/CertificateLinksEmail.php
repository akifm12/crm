<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CertificateLinksEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $companyName,
        public string $sessionTitle,
        public string $sessionDate,
        public Collection $attendees,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Training Certificates — ' . $this->sessionTitle,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.certificate_links',
        );
    }
}
