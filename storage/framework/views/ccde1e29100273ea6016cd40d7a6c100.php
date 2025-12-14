<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
      <title><?php echo e(config('app.name', 'Laravel')); ?></title>
      
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
      <link rel="stylesheet" href="<?php echo e(asset('css/components/dropdown.css')); ?>">

    
    <link rel="stylesheet" href="<?php echo e(asset('css/forms.css')); ?>">

      <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
      
      <?php if(function_exists('\Livewire\Livewire::styles')): ?>
          <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

      <?php else: ?>
          <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

      <?php endif; ?>
  </head>
  <body class="antialiased">
      <?php echo $__env->make('partials.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> 

      <main>
          
          <?php echo $slot ?? $__env->yieldContent('content'); ?>

      </main>

      <?php echo $__env->yieldContent('info-content'); ?>

      <?php echo $__env->make('partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

      
      <?php if(auth()->guard()->guest()): ?>
      <div id="loginModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.6); z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center;">
          <div style="background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); max-width: 450px; width: 90%; max-height: 90vh; overflow-y: auto; animation: modalFadeIn 0.3s ease-out;">
              <div style="padding: 2rem;">
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                      <h2 style="font-size: 1.5rem; font-weight: 600; margin: 0;">Log in</h2>
                      <button onclick="closeLoginModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280;">&times;</button>
                  </div>
                  <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('pages.auth.login');

$__html = app('livewire')->mount($__name, $__params, 'lw-796321802-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
              </div>
          </div>
      </div>

      
      <div id="registerModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.6); z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center;">
          <div style="background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); max-width: 450px; width: 90%; max-height: 90vh; overflow-y: auto; animation: modalFadeIn 0.3s ease-out;">
              <div style="padding: 2rem;">
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                      <h2 style="font-size: 1.5rem; font-weight: 600; margin: 0;">Register</h2>
                      <button onclick="closeRegisterModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280;">&times;</button>
                  </div>
                  <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('pages.auth.register');

$__html = app('livewire')->mount($__name, $__params, 'lw-796321802-1', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
              </div>
          </div>
      </div>
      <?php endif; ?>

      <style>
          @keyframes modalFadeIn {
              from {
                  opacity: 0;
                  transform: scale(0.95) translateY(-20px);
              }
              to {
                  opacity: 1;
                  transform: scale(1) translateY(0);
              }
          }
      </style>

      <script>
          function openLoginModal() {
              document.getElementById('loginModal').style.display = 'flex';
              document.body.style.overflow = 'hidden';
          }
          
          function closeLoginModal() {
              document.getElementById('loginModal').style.display = 'none';
              document.body.style.overflow = 'auto';
          }

          function openRegisterModal() {
              document.getElementById('registerModal').style.display = 'flex';
              document.body.style.overflow = 'hidden';
          }
          
          function closeRegisterModal() {
              document.getElementById('registerModal').style.display = 'none';
              document.body.style.overflow = 'auto';
          }
          
          // Close modal when clicking outside
          document.getElementById('loginModal')?.addEventListener('click', function(e) {
              if (e.target === this) {
                  closeLoginModal();
              }
          });

          document.getElementById('registerModal')?.addEventListener('click', function(e) {
              if (e.target === this) {
                  closeRegisterModal();
              }
          });
      </script>

      
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

    

      
      
      <?php if(function_exists('\Livewire\Livewire::scripts')): ?>
          <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

      <?php else: ?>
          <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

      <?php endif; ?>

      
      <script>
          (function(){
              try{
                  if(window.Livewire && !window.livewire){
                      var methods = ['start','connect','hydrate','initialize','rescan','scan','attach','mount'];
                      for(var i=0;i<methods.length;i++){
                          var m = methods[i];
                          try{
                              if(typeof window.Livewire[m] === 'function'){
                                  window.Livewire[m]();
                                  break;
                              }
                          }catch(e){}
                      }

                      // If still not set, alias the global so other code can reference it.
                      if(!window.livewire){
                          try{ window.livewire = window.Livewire; }catch(e){}
                      }
                  }
              }catch(e){ console && console.debug && console.debug('livewire init helper error', e); }
          })();
      </script>

      <?php echo $__env->yieldPushContent('scripts'); ?>
  </body>
</html>
<?php /**PATH C:\Users\Marc Ian C. Young\laravel\fceerweb\resources\views/layouts/app.blade.php ENDPATH**/ ?>