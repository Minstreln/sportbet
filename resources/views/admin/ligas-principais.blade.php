@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-trophy "></i> Ligas <small>Suas ligas principais</small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li class="active">Ligas Principais</li>
    </ol>

@stop

@section('content')
  <list-ligas-main-component/>
@stop