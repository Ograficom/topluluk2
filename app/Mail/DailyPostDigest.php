<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

class DailyPostDigest extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  Collection<int, \App\Models\Post>  $posts
     */
    public function __construct(
        public readonly User $recipient,
        public readonly Collection $posts,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ografinin günlük gönderi özeti',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-post-digest',
            text: 'emails.daily-post-digest-text',
            with: [
                'unsubscribeUrl' => URL::signedRoute('digest.unsubscribe', [
                    'userId' => $this->recipient->getKey(),
                    'hash' => sha1(mb_strtolower(trim((string) $this->recipient->daily_digest_email))),
                ]),
                'preferencesUrl' => route('dashboard.notifications'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
