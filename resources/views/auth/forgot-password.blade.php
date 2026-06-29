<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Password dimenticata — A.V.R. Informatica</title>
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
      <h2 class="login-title">Password dimenticata</h2>
      <p class="text-muted mb-4">Inserisci la tua email e ti invieremo un link per reimpostare la password.</p>

      @if(session('success'))
        <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
          <label class="form-label fw-500">Email</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                   placeholder="admin@avrinformatica.it" required autofocus>
          </div>
        </div>
        <button type="submit" class="btn btn-avr w-100 py-2">
          <i class="bi bi-send me-2"></i>Invia link di reset
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
</body>
</html>
