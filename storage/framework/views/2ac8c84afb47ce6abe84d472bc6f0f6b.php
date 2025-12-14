<?php if(session('success') || session('error') || session('temp_password')): ?>
  <div class="container mt-3" style="max-width: 960px;">
    <?php if(session('success')): ?>
      <div class="alert alert-success mb-2"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
      <div class="alert alert-danger mb-2"><?php echo e(session('error')); ?></div>
    <?php endif; ?>
    <?php if(session('temp_password')): ?>
      <div class="alert alert-info mb-2">
        Temporary password: <strong><?php echo e(session('temp_password')); ?></strong>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?><?php /**PATH C:\Users\Marc Ian C. Young\laravel\fceerweb\resources\views/partials/flash.blade.php ENDPATH**/ ?>