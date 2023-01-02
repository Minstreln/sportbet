@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-area-chart "></i> Gerenciar Mercados <small>Organize seus mercados por cambistas</small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li class="active">Gerenciar Mercados Cambistas</li>
    </ol>

@stop

@section('content')

    
  <mercados-cambista-component></mercados-cambista-component>
@stop