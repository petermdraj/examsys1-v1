<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminBulkAnnouncementMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $announcementSubject,
        public string $announcementBody,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->announcementSubject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin-bulk-announcement',
            with: [
                'user'    => $this->user,
                'body'    => $this->announcementBody,
            ],
        );
    }
}
