@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-trophy "></i> Taxas(Seninha) <small>Gerencie aqui as taxas loto</small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('cotacao-loto-sena')}}"><i class="fa fa-qrcode"></i> Página Inicial</a></li>
            <li class="active">Gerenciar Taxas Loto</li>
    </ol>

@stop

@section('content')
    <taxa-loto-sena></taxa-loto-sena>
@stop