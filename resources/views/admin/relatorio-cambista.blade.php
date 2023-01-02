@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-money"></i> Relatório <small>Cambistas do sistema </small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li class="active">Relatório-Cambistas</li>
    </ol>

@stop

@section('content')

    <relatorio-cambista-component/>
  
@stop