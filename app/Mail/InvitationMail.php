<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $acceptanceUrl) {}

    public function build(): self
    {
        return $this->subject('Sembark invitation')->view('emails.invitation');
    }
}
