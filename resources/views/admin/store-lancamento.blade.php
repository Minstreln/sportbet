@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-cloud-upload "></i> Lançamento <small>Lançamento de valores </small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li class="active">Lançamentos</li>
    </ol>

@stop

@section('content')

<lancamentos-component></lancamentos-component>

@stop