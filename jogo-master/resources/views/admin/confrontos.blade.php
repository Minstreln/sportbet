@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-futbol-o "></i> Confrontos <small>Cadastrados no Sistema</small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li class="active">Gerenciar Confrontos</li>
    </ol>

@stop

@section('content')

    <confrontos-component></confrontos-component>
  
@stop