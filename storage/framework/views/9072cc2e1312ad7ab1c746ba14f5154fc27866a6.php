<?php $__env->startSection('title', config('adminlte.title_adm_geral')); ?>

<?php $__env->startSection('content_header'); ?>
    
    <h1><i class="fa fa-tags "></i> Gerenciar Ligas (Bloqueadas) <small>Gerencie Ligas </small></h1>

    <ol class="breadcrumb">
            <li><a href="<?php echo e(route('home')); ?>"><i class="fa fa-dashboard"></i> Página Inicial</a></li>
            <li class="active">Gerenciar Ligas</li>
    </ol>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<gerenciar-ligas-component></gerenciar-ligas-component>
  
<?php $__env->stopSection(); ?>
<?php echo $__env->make('adminlte::page', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>