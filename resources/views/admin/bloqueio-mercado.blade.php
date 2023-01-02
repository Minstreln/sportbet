@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-sort-amount-asc "></i> Mercados <small>Gerenciar Mercados </small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li class="active">Mercados</li>
    </ol>

@stop

@section('content')

    <mercados-component></mercados-component>
  
@stop