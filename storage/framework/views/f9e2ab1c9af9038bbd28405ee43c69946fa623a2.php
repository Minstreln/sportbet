<?php $__env->startSection('title', config('adminlte.title_adm_geral')); ?>

<?php $__env->startSection('content_header'); ?>
    
    <h1><i class="fa fa-trophy "></i> Taxas(Seninha) <small>Gerencie aqui as taxas loto</small></h1>

    <ol class="breadcrumb">
            <li><a href="<?php echo e(route('cotacao-loto-sena')); ?>"><i class="fa fa-qrcode"></i> Página Inicial</a></li>
            <li class="active">Gerenciar Taxas Loto</li>
    </ol>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <taxa-loto-sena></taxa-loto-sena>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('adminlte::page', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>