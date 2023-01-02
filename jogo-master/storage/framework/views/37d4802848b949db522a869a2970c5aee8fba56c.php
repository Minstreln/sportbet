<?php $__env->startSection('adminlte_css'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('vendor/adminlte/plugins/iCheck/square/blue.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/adminlte/css/auth.css')); ?>">
    <?php echo $__env->yieldContent('css'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('body_class', 'login-page'); ?>

<?php $__env->startSection('body'); ?>

  
    <div class="login-box">
         
      
        <div class="login-box-body">
            <div class="login-logo">
                    <a href="./"><img  width="200" height="160" src="<?php echo e(url('img/logo.png')); ?>"></a>
            </div>
            <!-- /.login-logo -->
            
            <form action="<?php echo e(url(config('adminlte.login_url', 'login'))); ?>" method="post">
                <?php echo csrf_field(); ?>


                <div class="form-group has-feedback <?php echo e($errors->has('username') ? 'has-error' : ''); ?>">
                    <input type="text" name="username" class="form-control" value="<?php echo e(old('username')); ?>"
                           placeholder="<?php echo e(trans('adminlte::adminlte.username')); ?>">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                    <?php if($errors->has('username')): ?>
                        <span class="help-block">
                            <strong><?php echo e($errors->first('username')); ?></strong>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="form-group has-feedback <?php echo e($errors->has('password') ? 'has-error' : ''); ?>">
                    <input type="password" name="password" class="form-control"
                           placeholder="<?php echo e(trans('adminlte::adminlte.password')); ?>">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    <?php if($errors->has('password')): ?>
                        <span class="help-block">
                            <strong><?php echo e($errors->first('password')); ?></strong>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="row">
                   
                    <!-- /.col -->
                    <div class="col-xs-4">
                        <button type="submit"
                                class="btn btn-primary btn-block btn-flat"><?php echo e(trans('adminlte::adminlte.sign_in')); ?></button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>
            <div class="auth-links">
                    <a href="<?php echo e(url(config('adminlte.password_reset_url', 'password/reset'))); ?>"
                    class="text-center"
                 ><?php echo e(trans('adminlte::adminlte.i_forgot_my_password')); ?></a>
                <br>
               
            </div>
        </div>
        <!-- /.login-box-body -->
    </div><!-- /.login-box -->

<?php $__env->stopSection(); ?>

<?php $__env->startSection('adminlte_js'); ?>
    <script src="<?php echo e(asset('vendor/adminlte/plugins/iCheck/icheck.min.js')); ?>"></script>
    <script>
  
    </script>
    <?php echo $__env->yieldContent('js'); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>