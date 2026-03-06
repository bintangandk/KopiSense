<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetTokenMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $username,
        public string $token,
        public int $expireMinutes
    ) {}

    public function build(): self
    {
        return $this->subject('Token Reset Password')
            ->view('emails.password-reset-token');
    }
}
