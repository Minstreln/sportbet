@extends('adminlte::page')

@section('title', config('adminlte.title_adm_geral'))

@section('content_header')
    
    <h1><i class="fa fa-dashboard "></i> Dashboard <small> {{config('adminlte.version_system')}}</small></h1>

@stop

@section('content')
<home-component></home-component>
  
@stop