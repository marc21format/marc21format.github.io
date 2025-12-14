@if (session('success') || session('error') || session('temp_password'))
  <div class="container mt-3" style="max-width: 960px;">
    @if (session('success'))
      <div class="alert alert-success mb-2">{{ session('success') }}</div>
    @endif
    @if (session('error'))
      <div class="alert alert-danger mb-2">{{ session('error') }}</div>
    @endif
    @if (session('temp_password'))
      <div class="alert alert-info mb-2">
        Temporary password: <strong>{{ session('temp_password') }}</strong>
      </div>
    @endif
  </div>
@endif