@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-tags "></i> Bilhetes <small>Pesquisar no sistema </small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li class="active">bilhetes</li>
    </ol>

@stop

@section('content')

<bilhete-adm-component></bilhete-adm-component>
  
@stop