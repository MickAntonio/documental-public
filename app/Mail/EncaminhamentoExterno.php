<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class EncaminhamentoExterno extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $encaminhamento;
    public $registo;
    public $anexos = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($encaminhamento)
    {
        $this->encaminhamento = $encaminhamento;
        $this->registo = $encaminhamento->registo;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            from: new Address(env('MAIL_FROM_ADDRESS'), $this->encaminhamento->utilizador->name),
            to: $this->encaminhamento->to,
            cc: $this->encaminhamento->cc,
            bcc: $this->encaminhamento->bcc,
            subject: 'Encaminhamento: ' . $this->registo->assunto . ' | ' . $this->registo->referencia
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            markdown: 'emails.encaminhamento-externo',
        );
    }
 
    /**
     * Get the attachments for the message.
     *
     * @return \Illuminate\Mail\Mailables\Attachment[]
     */
    public function attachments()
    {
        foreach ($this->registo->anexos as $anexo) {
            $this->anexos []= Attachment::fromStorage($anexo->localizacao)
            ->as($anexo->nome . $anexo->extensao);
        }
        return $this->anexos;
    }
}
