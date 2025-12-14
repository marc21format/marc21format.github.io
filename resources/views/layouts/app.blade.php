<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <title>{{ config('app.name', 'Laravel') }}</title>
      
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
      <link rel="stylesheet" href="{{ asset('css/components/dropdown.css') }}">

    {{-- Reusable form styles (modern rounded inputs, selects, placeholders) --}}
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}">

      @vite(['resources/css/app.css', 'resources/js/app.js'])
      {{-- Livewire styles (ensures Livewire assets are present even if auto-injection doesn't run) --}}
      @if (function_exists('\Livewire\Livewire::styles'))
          @livewireStyles
      @else
          @livewireStyles
      @endif
  </head>
  <body class="antialiased">
      @include('partials.navbar')
      @include('partials.flash') {{-- Show success/error/temp password --}}

      <main>
          {{-- Support Livewire page components via $slot, fall back to traditional @section('content') --}}
          {!! $slot ?? $__env->yieldContent('content') !!}
      </main>

      @yield('info-content')

      @include('partials.footer')

      {{-- Login Modal --}}
      @guest
      <div id="loginModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.6); z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center;">
          <div style="background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); max-width: 450px; width: 90%; max-height: 90vh; overflow-y: auto; animation: modalFadeIn 0.3s ease-out;">
              <div style="padding: 2rem;">
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                      <h2 style="font-size: 1.5rem; font-weight: 600; margin: 0;">Log in</h2>
                      <button onclick="closeLoginModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280;">&times;</button>
                  </div>
                  @livewire('pages.auth.login')
              </div>
          </div>
      </div>

      {{-- Register Modal --}}
      <div id="registerModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.6); z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center;">
          <div style="background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); max-width: 450px; width: 90%; max-height: 90vh; overflow-y: auto; animation: modalFadeIn 0.3s ease-out;">
              <div style="padding: 2rem;">
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                      <h2 style="font-size: 1.5rem; font-weight: 600; margin: 0;">Register</h2>
                      <button onclick="closeRegisterModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280;">&times;</button>
                  </div>
                  @livewire('pages.auth.register')
              </div>
          </div>
      </div>
      @endguest

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

      {{-- Core JS libraries --}}
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

    {{-- Alpine is loaded via Vite (resources/js/app.js) to ensure a single, managed instance --}}

      {{-- This is where Blade pushes scripts from individual views --}}
      {{-- Livewire scripts --}}
      @if (function_exists('\Livewire\Livewire::scripts'))
          @livewireScripts
      @else
          @livewireScripts
      @endif

      {{-- Ensure the Livewire client is initialized in edge cases where the
           library is present but the runtime client (window.livewire) was
           not attached (prevents forms falling back to native submits). This
           attempts several common initialization entrypoints and then
           aliases window.livewire to window.Livewire as a last resort.
           Remove or adjust if upstream Livewire changes APIs. --}}
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

      @stack('scripts')
  </body>
</html>
