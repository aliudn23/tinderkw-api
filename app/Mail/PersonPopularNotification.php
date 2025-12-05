<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Person;

class PersonPopularNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Person $person;

    /**
     * Create a new message instance.
     */
    public function __construct(Person $person)
    {
        $this->person = $person;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Popular Person Arrived - ' . $this->person->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.person-popular',
            with: [
                'personName' => $this->person->name,
                'personAge' => $this->person->age,
                'likeCount' => $this->person->like_count,
                'personId' => $this->person->id,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
