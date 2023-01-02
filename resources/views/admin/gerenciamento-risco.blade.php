@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-balance-scale "></i> Gerenciamento de Riscos <small>Sistema </small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li class="active">gerenciamento-risco</li>
    </ol>

@stop

@section('content')

<gerenciar-riscos-component></gerenciar-riscos-component>
  
@stop