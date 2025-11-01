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

class EncaminhamentoPendencia extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $encaminhamento;
    public $registo;
    public $emails = [];
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

        foreach ($this->encaminhamento->destinatarios as $destinatario) {

            if($destinatario->tipo=='UTILIZADOR'){

                if(!in_array($destinatario->utilizador->email, $this->emails)){
                    $this->emails[] = $destinatario->utilizador->email;
                }

            }else if($destinatario->tipo=='GRUPO'){

                $utilizadores = collect(KeycloakClient()->getGroupMembers(['id' => $destinatario->grupoId]))->values();
                
                foreach ($utilizadores as $utilizador) {
                    if(!in_array($utilizador['email'], $this->emails)){
                        $this->emails[] = $utilizador['email'];
                    }
                }
            }else if($destinatario->tipo=='ENTIDADE'){
                $this->emails[] = $destinatario->entidade->email;               
            }
        }
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
            to: $this->emails,
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
            markdown: 'emails.encaminhamento-pendencia',
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
