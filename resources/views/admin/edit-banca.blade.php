@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-edit "></i> Editar <small>Atualize os dados</small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li class="active">Editar Dados</li>
    </ol>

@stop

@section('content')

<editar-banca-component></editar-banca-component>
  
@stop