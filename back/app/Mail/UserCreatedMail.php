<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserCreatedMail extends Mailable
{
    use Queueable, SerializesModels;
    public  $username;
    public  $password;
    public $name;
    public function __construct($nombre, $username, $password)
    {
        $this->name = $nombre;
        $this->username = $username;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Su cuenta ha sido creada')
            ->view('emails.user_created')
            ->with([
                'username' => $this->username,
                'password' => $this->password,
                'name' => $this->name,
            ]);
    }
}
