@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-line-chart"></i> Mapa de apostas <small>Jogos em aberto </small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li class="active">mapa-apostas</li>
    </ol>

@stop

@section('content')

<mapa-aposta-component></mapa-aposta-component>

  
@stop