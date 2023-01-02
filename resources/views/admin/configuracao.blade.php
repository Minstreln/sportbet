@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-dashboard "></i> Configurações <small>Limites da banca</small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li><a href="{{route('configuracoes')}}"><i class="fa fa-cogs"></i> Configurações</a></li>
            <li class="active">Configurar</li>
    </ol>

@stop

@section('content')

<configuracao-banca-component></configuracao-banca-component>
  
@stop