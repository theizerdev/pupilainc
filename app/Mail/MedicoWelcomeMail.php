<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Medico;
use App\Models\Empresa;
use App\Models\Sucursal;

class MedicoWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $medico;
    public $empresa;
    public $sucursal;
    public $password;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Medico $medico, Empresa $empresa, Sucursal $sucursal, $password = 'password')
    {
        $this->user = $user;
        $this->medico = $medico;
        $this->empresa = $empresa;
        $this->sucursal = $sucursal;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido al Sistema Médico - ' . $this->empresa->razon_social,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.medico-welcome',
            with: [
                'user' => $this->user,
                'medico' => $this->medico,
                'empresa' => $this->empresa,
                'sucursal' => $this->sucursal,
                'password' => $this->password,
            ]
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