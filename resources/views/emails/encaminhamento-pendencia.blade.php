@component('mail::message')
    
{{ $encaminhamento->mensagem }}

@component('mail::button', ['url' => 'http://localhost:4300/registo/entradas/' . $registo->id])
    Ver mais
@endcomponent

@endcomponent

