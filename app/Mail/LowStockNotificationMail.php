<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $item;
    public $quantity;
    public $warehouse;

    public function __construct($item, $quantity, $warehouse)
    {
        $this->item = $item;
        $this->quantity = $quantity;
        $this->warehouse = $warehouse;
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'low stock' . $this->item->item_name,
        );
    }

    // هون بنحدد مسار ملف الـ HTML (Blade)
    public function content(): Content
    {
        return new Content(
            view: 'emails.low_stock'
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
