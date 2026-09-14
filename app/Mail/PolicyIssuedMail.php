<?php

namespace App\Mail;

use App\Models\RcaPolicy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Class PolicyIssuedMail
 *
 * Mailable object with the responsability of assemblying electronic notification of confirmation.
 * Uses the direct attachment mechanism from the memory (Attachment::fromData)
 * excluding the writing of temporary files on the server disk before transmission.
 */
class PolicyIssuedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     *
     *
     * @param RcaPolicy $policy Contract instance saved in the database
     * @param string $documentContent Rendered body of the policy
     */
    public function __construct(
        public RcaPolicy $policy,
        public string $documentContent
    ) {}

    /**
     * Configs the elements of the sender
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Policy issue confirmation for RCA - Series {$this->policy->policy_series} Nr. {$this->policy->policy_number}",
        );
    }

    /**
     * Sets the visual Blade template which constructs the message body.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.policy_issued',
            with: [
                'policy' => $this->policy,
            ]
        );
    }

    /**
     * Attaches the official document as downloadable stream binary/text.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => $this->documentContent,
                "RCA_Policy_{$this->policy->policy_series}_{$this->policy->policy_number}.html"
            )->withMime('text/html'),
        ];
    }
}
