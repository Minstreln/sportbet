@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-sort-amount-asc "></i> Odds <small>Gerenciar Odds Cambistas </small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li class="active">Odds Cambistas</li>
    </ol>

@stop

@section('content')

    <odds-cambista-component></odds-cambista-component>
    
@stop