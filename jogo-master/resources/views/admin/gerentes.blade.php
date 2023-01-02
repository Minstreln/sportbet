@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-users "></i> Gerentes <small>Cadastrados no sistema</small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li class="active">Gerentes</li>
    </ol>

@stop

@section('content')

<gerentes-component></gerentes-component>
  
@stop