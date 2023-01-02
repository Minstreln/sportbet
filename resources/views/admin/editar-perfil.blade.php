@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-users "></i> Perfil <small>Editar </small></h1>

    <ol class="breadcrumb">
            <li><a href="{{route('home')}}"><i class="fa fa-users"></i> Página Inicial</a></li>
            <li class="active">editar-perfil</li>
    </ol>

@stop

@section('content')

<edt-perfil-adm-component></edt-perfil-adm-component>
  
@stop