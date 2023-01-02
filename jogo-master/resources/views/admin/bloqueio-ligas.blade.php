@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-tags "></i> Gerenciar Ligas (Bloqueadas) <small>Gerencie Ligas </small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li class="active">Gerenciar Ligas</li>
    </ol>

@stop

@section('content')

<gerenciar-ligas-component></gerenciar-ligas-component>
  
@stop