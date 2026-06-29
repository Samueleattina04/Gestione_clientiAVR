<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Reimposta Password — A.V.R. Informatica</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body class="login-page">
<div class="login-container">
  <div class="login-card">
    <div class="login-header">
      <img src="{{ asset('img/banner.png') }}" alt="A.V.R. Informatica" class="login-banner">
    </div>
    <div class="login-body">
      <h2 class="login-title">Reimposta Password</h2>
      <p class="text-muted mb-4">Scegli una nuova password per il tuo account.</p>

      @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-3">
          <label class="form-label fw-500">Email</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control" value="{{ old('email', $email) }}" required>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label fw-500">Nuova password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="passInput" class="form-control" placeholder="Minimo 8 caratteri" required minlength="8">
            <button type="button" class="btn btn-outline-secondary" onclick="togglePass()"><i class="bi bi-eye" id="eyeIcon"></i></button>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label fw-500">Conferma nuova password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input type="password" name="password_confirmation" class="form-control" placeholder="Ripeti la password" required>
          </div>
        </div>
        <button type="submit" class="btn btn-avr w-100 py-2">
          <i class="bi bi-check-lg me-2"></i>Reimposta Password
        </button>
      </form>

      <div class="text-center mt-3">
        <a href="{{ route('login') }}" class="text-muted small">
          <i class="bi bi-arrow-left me-1"></i>Torna al login
        </a>
      </div>
    </div>
  </div>
  <p class="text-center text-white-50 mt-4 small">&copy; {{ date('Y') }} A.V.R. Informatica — Assistenza e Consulenza IT</p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>function togglePass(){const i=document.getElementById('passInput'),e=document.getElementById('eyeIcon');i.type=i.type==='password'?'text':'password';e.className=i.type==='password'?'bi bi-eye':'bi bi-eye-slash';}</script>
</body>
</html>
