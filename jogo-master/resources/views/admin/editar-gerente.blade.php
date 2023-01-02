@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-users "></i> Gerente <small>Editar </small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-users"></i> Página Inicial</a></li>
            <li class="active">editar/gerente</li>
    </ol>

@stop

@section('content')

<editar-gerentes-component></editar-gerentes-component>
  
@stop