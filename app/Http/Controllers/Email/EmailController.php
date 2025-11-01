<?php

namespace App\Http\Controllers\Email;

use App\Jobs\SendEmails;
use App\Jobs\ProcessPodcast;
use Illuminate\Http\Request;
use App\Models\Encaminhamento;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\EncaminhamentoPendencia;

class EmailController extends Controller
{
    

     /**
     * SendMail a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function senMail()
    {
        $encaminhamento = Encaminhamento::with([
            'registo',
            'utilizador',
            'destinatarios',
            'destinatarios.utilizador'
        ])->find(1);

        Mail::send(new EncaminhamentoPendencia($encaminhamento));

        return response('Enviado com successo');
    }
}
