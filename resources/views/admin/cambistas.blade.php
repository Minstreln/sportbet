@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-users "></i> Cambistas <small>Cadastrados no sistema</small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li class="active">Cambistas</li>
    </ol>

@stop

@section('content')

<cambistas-component></cambistas-component>
  
@stop