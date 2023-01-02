@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-bank "></i> Caixa (Gerentes) <small>Relatórios </small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li class="active">caixa-adm-gerente</li>
    </ol>

@stop

@section('content')

<finacneiro-adm-gerente-component></finacneiro-adm-gerente-component>
  
@stop