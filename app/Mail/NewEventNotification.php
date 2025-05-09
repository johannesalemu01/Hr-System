<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class NewEventNotification extends Mailable
{
    use SerializesModels;

    public Event $event;

    /**
     * Create a new message instance.
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Event Announcement: ' . $this->event->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.events.new_notification',
            with: [
                'eventTitle' => $this->event->title,
                'eventDate' => Carbon::parse($this->event->event_date)->format('F j, Y \a\t g:i A'),
                'eventType' => ucfirst($this->event->type),
                'eventDescription' => $this->event->description,
                'url' => route('events.show', $this->event->id),
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
