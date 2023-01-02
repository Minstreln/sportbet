@component('mail::message')
# Alerta de Aposta {{ $bilhete->cupom}}
 
Clique no botão e tenha acesso a aposta.
 
@component('mail::button', ['url' => config('app.url').'/api/bilhete/'.$bilhete->id])
Acessar o Site
@endcomponent
 
Obrigado,
{{ config('app.name') }}
@endcomponent