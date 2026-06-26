@extends('layouts.app')
@section('title', 'Dashboard')
@section('page_title') <i class="bi bi-grid-1x2-fill me-2 text-avr"></i> Dashboard @endsection
@section('topbar_actions')
  <a href="{{ route('customers.create') }}" class="btn btn-avr btn-sm"><i class="bi bi-person-plus-fill me-1"></i> Nuovo Cliente</a>
@endsection
@section('content')
<div class="welcome-banner mb-4">
  <div class="row align-items-center">
    <div class="col">
      <h4 class="mb-1">Benvenuto, {{ auth()->user()->name }}!</h4>
      <p class="mb-0 opacity-75">{{ now()->locale('it')->isoFormat('dddd D MMMM YYYY') }} — Pannello A.V.R. Informatica</p>
    </div>
    <div class="col-auto d-none d-md-block">
      <img src="{{ asset('img/logo.png') }}" height="60" alt="A.V.R. Informatica" style="border-radius:8px;background:#fff;padding:4px">
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3">
    <div class="kpi-card kpi-blue">
      <div class="kpi-icon"><i class="bi bi-people-fill"></i></div>
      <div class="kpi-value">{{ $totalCustomers }}</div>
      <div class="kpi-label">Clienti Attivi</div>
      <a href="{{ route('customers.index') }}" class="kpi-link">Vedi tutti <i class="bi bi-arrow-right"></i></a>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="kpi-card kpi-green">
      <div class="kpi-icon"><i class="bi bi-collection-fill"></i></div>
      <div class="kpi-value">{{ $totalSubs }}</div>
      <div class="kpi-label">Abbonamenti Attivi</div>
      <a href="{{ route('subscriptions.index') }}" class="kpi-link">Vedi tutti <i class="bi bi-arrow-right"></i></a>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="kpi-card kpi-orange">
      <div class="kpi-icon"><i class="bi bi-clock-history"></i></div>
      <div class="kpi-value">{{ $expiring90 }}</div>
      <div class="kpi-label">Scadono in 90 giorni</div>
      <a href="{{ route('reports.expiring', ['days'=>90]) }}" class="kpi-link">Dettaglio <i class="bi bi-arrow-right"></i></a>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="kpi-card kpi-red">
      <div class="kpi-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
      <div class="kpi-value">{{ $expiring30 }}</div>
      <div class="kpi-label">Scadono in 30 giorni</div>
      <a href="{{ route('reports.expiring', ['days'=>30]) }}" class="kpi-link">Urgenti <i class="bi bi-arrow-right"></i></a>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-12 col-xl-5">
    <div class="card-avr h-100">
      <div class="card-header-avr"><i class="bi bi-currency-euro me-2"></i> Ricavi Stimati</div>
      <div class="p-4">
        <div class="row text-center">
          <div class="col-6">
            <div class="revenue-box">
              <div class="revenue-label">Mensile</div>
              <div class="revenue-value">€{{ number_format($monthlyRevenue, 2, ',', '.') }}</div>
            </div>
          </div>
          <div class="col-6">
            <div class="revenue-box revenue-box-highlight">
              <div class="revenue-label">Annuale</div>
              <div class="revenue-value">€{{ number_format($yearlyRevenue, 2, ',', '.') }}</div>
            </div>
          </div>
        </div>
        @if($licenseStats->isNotEmpty())
        <hr class="my-3">
        <div class="small text-muted mb-2">Distribuzione licenze attive</div>
        @foreach($licenseStats as $stat)
        <div class="mb-2">
          <div class="d-flex justify-content-between small mb-1">
            <span class="text-truncate me-2">{{ $stat->name }}</span>
            <span class="fw-600">{{ $stat->count }}</span>
          </div>
          <div class="progress" style="height:6px">
            <div class="progress-bar bg-avr" style="width:{{ $totalSubs > 0 ? round($stat->count / $totalSubs * 100) : 0 }}%"></div>
          </div>
        </div>
        @endforeach
        @endif
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-7">
    <div class="card-avr h-100">
      <div class="card-header-avr">
        <i class="bi bi-bell-fill me-2"></i> Abbonamenti in Scadenza
        <span class="badge bg-avr ms-2">{{ $expiring180 }}</span>
      </div>
      @if($recentSubs->isNotEmpty())
      <div class="table-responsive">
        <table class="table table-hover avr-table mb-0">
          <thead><tr><th>Cliente</th><th>Licenza</th><th>Scadenza</th><th>Stato</th><th></th></tr></thead>
          <tbody>
          @foreach($recentSubs as $sub)
          <tr>
            <td>
              <a href="{{ route('customers.show', $sub->customer) }}" class="fw-500 text-dark text-decoration-none">{{ $sub->customer->full_name }}</a>
              @if($sub->customer->company)<div class="text-muted small">{{ $sub->customer->company }}</div>@endif
            </td>
            <td><span class="badge-license">{{ $sub->licenseType->name }}</span></td>
            <td><span class="fw-500">{{ $sub->end_date->format('d/m/Y') }}</span><div class="text-muted small">{{ $sub->days_to_expiry }}gg</div></td>
            <td>
              <span class="badge-expiry badge-{{ $sub->expiry_class }}">
                @if($sub->expiry_class === 'critical') Critico
                @elseif($sub->expiry_class === 'warning') Attenzione
                @elseif($sub->expiry_class === 'soon') Presto
                @else OK @endif
              </span>
            </td>
            <td>
              <form method="POST" action="{{ route('subscriptions.remind', $sub) }}" class="d-inline">@csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Invia promemoria"><i class="bi bi-envelope"></i></button>
              </form>
            </td>
          </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      @else
      <div class="text-center py-5 text-muted">
        <i class="bi bi-check-circle-fill fs-1 text-success"></i>
        <p class="mt-2">Nessuna scadenza imminente!</p>
      </div>
      @endif
    </div>
  </div>
</div>

<div class="card-avr">
  <div class="card-header-avr"><i class="bi bi-lightning-fill me-2"></i> Azioni Rapide</div>
  <div class="p-3">
    <div class="row g-2">
      <div class="col-6 col-md-3"><a href="{{ route('customers.create') }}" class="quick-action-btn"><i class="bi bi-person-plus-fill"></i><span>Nuovo Cliente</span></a></div>
      <div class="col-6 col-md-3"><a href="{{ route('subscriptions.create') }}" class="quick-action-btn"><i class="bi bi-plus-circle-fill"></i><span>Nuovo Abbonamento</span></a></div>
      <div class="col-6 col-md-3"><a href="{{ route('subscriptions.export') }}" class="quick-action-btn"><i class="bi bi-file-earmark-excel-fill"></i><span>Esporta Excel</span></a></div>
      <div class="col-6 col-md-3"><a href="{{ route('reports.expiring') }}" class="quick-action-btn"><i class="bi bi-clock-history"></i><span>Report Scadenze</span></a></div>
    </div>
  </div>
</div>
@endsection
