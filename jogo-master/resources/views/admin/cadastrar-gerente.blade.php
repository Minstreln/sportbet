@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-users "></i> Gerente <small>Cadastrar </small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li><a href="{{route('gerentes')}}"><i class="fa fa-user"></i> Gerentes</a></li>
            <li class="active">Cadastrar</li>
    </ol>

@stop

@section('content')

<cadastro-gerentes-component></cadastro-gerentes-component>
  
@stop