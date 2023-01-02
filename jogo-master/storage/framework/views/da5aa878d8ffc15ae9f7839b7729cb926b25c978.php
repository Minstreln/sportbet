<?php $__env->startSection('title', config('adminlte.title_adm_geral')); ?>

<?php $__env->startSection('content_header'); ?>
    
    <h1><i class="fa fa-dashboard "></i> Dashboard <small> <?php echo e(config('adminlte.version_system')); ?></small></h1>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<home-component></home-component>
  
<?php $__env->stopSection(); ?>
<?php echo $__env->make('adminlte::page', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>