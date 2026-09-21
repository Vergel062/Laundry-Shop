<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;

    public function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'LaundryCare - Ready for Pick Up! (Ref: ' . $this->transaction->ref_number . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_ready', // Handa nating gagawin ang blade file na ito
        );
    }

    public function attachments(): array
    {
        return [];
    }
}