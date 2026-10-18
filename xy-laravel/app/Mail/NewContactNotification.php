<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewContactNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Contact $contact;

    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouveau contact depuis votre boutique XY',
            from: config('mail.from.address', 'noreply@xy-cm.com'),
            replyTo: $this->contact->email ?? config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-contact',
            with: [
                'contact' => $this->contact,
                'tenant' => $this->contact->tenant,
                'publicUrl' => $this->contact->tenant->public_url,
            ],
        );
    }
}
