@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-map-o "></i> Regulamento <small>Editar regulamento do sistema </small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li class="active">Editar Regulamento</li>
    </ol>

@stop

@section('content')

<regulamento-component></regulamento-component>

@stop