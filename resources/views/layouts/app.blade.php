<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'A.V.R. Informatica') — Gestionale</title>
  <link rel="icon" href="{{ asset('img/icon.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="{{ asset('css/style.css') }}" rel="stylesheet">
  @stack('styles')
</head>
<body>

<div class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <img src="{{ asset('img/logo.png') }}" alt="A.V.R. Informatica" class="sidebar-logo">
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Principale</div>
    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <i class="bi bi-grid-1x2-fill"></i> Dashboard
    </a>
    <div class="nav-section-label mt-3">Gestione</div>
    <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
      <i class="bi bi-people-fill"></i> Clienti
    </a>
    <a href="{{ route('subscriptions.index') }}" class="nav-link {{ request()->routeIs('subscriptions.*') ? 'active' : '' }}">
      <i class="bi bi-collection-fill"></i> Abbonamenti
    </a>
    <a href="{{ route('licenses.index') }}" class="nav-link {{ request()->routeIs('licenses.*') ? 'active' : '' }}">
      <i class="bi bi-key-fill"></i> Licenze
    </a>
    <div class="nav-section-label mt-3">Report</div>
    <a href="{{ route('reports.expiring') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
      <i class="bi bi-clock-history"></i> In Scadenza
    </a>
    <div class="nav-section-label mt-3">Strumenti</div>
    <a href="{{ route('customers.import.form') }}" class="nav-link {{ request()->routeIs('customers.import*') ? 'active' : '' }}">
      <i class="bi bi-upload"></i> Importa Excel
    </a>
    <a href="{{ route('customers.export') }}" class="nav-link">
      <i class="bi bi-download"></i> Esporta Clienti
    </a>
    <a href="{{ route('subscriptions.export') }}" class="nav-link">
      <i class="bi bi-file-earmark-excel"></i> Esporta Abbonamenti
    </a>
  </nav>
  <div class="sidebar-footer">
    <div class="d-flex align-items-center gap-2">
      <div class="avatar-sm">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
      <div class="flex-grow-1 overflow-hidden">
        <div class="fw-600 text-truncate small text-white">{{ auth()->user()->name }}</div>
        <div style="font-size:11px;color:rgba(255,255,255,.5)">Amministratore</div>
      </div>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-sm text-danger p-1" title="Esci">
          <i class="bi bi-box-arrow-right fs-5"></i>
        </button>
      </form>
    </div>
  </div>
</div>

<div class="main-content" id="mainContent">
  <header class="topbar">
    <button class="sidebar-toggle btn btn-sm" id="sidebarToggle">
      <i class="bi bi-list fs-4"></i>
    </button>
    <div class="topbar-title">@yield('page_title')</div>
    <div class="topbar-actions">@yield('topbar_actions')</div>
  </header>

  <div class="px-4 pt-3">
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('info'))
      <div class="alert alert-info alert-dismissible fade show"><i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('danger'))
      <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('danger') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
  </div>

  <main class="page-content px-4 pb-5">
    @yield('content')
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/main.js') }}"></script>
@stack('scripts')
</body>
</html>
