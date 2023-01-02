@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-users "></i> Cambista <small>Cadastrar </small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li><a href="{{route('cambistas')}}"><i class="fa fa-user"></i> Cambistas</a></li>
            <li class="active">Cadastrar</li>
    </ol>

@stop

@section('content')

<cadastro-cambistas-component></cadastro-cambistas-component>
  
@stop