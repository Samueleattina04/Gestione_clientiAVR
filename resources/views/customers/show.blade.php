@extends('layouts.app')
@section('title', $customer->full_name)
@section('page_title')<i class="bi bi-person-fill me-2 text-avr"></i> {{ $customer->full_name }}@endsection
@section('topbar_actions')
  <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Modifica</a>
  <a href="{{ route('subscriptions.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-avr"><i class="bi bi-plus-circle me-1"></i>Aggiungi Abbonamento</a>
@endsection
@section('content')
<div class="row g-4">
  <div class="col-12 col-xl-4">
    <div class="card-avr">
      <div class="card-header-avr"><i class="bi bi-person-vcard me-2"></i> Dati Cliente</div>
      <div class="p-4 text-center">
        <div class="customer-avatar mx-auto mb-3">{{ strtoupper(substr($customer->first_name,0,1).substr($customer->last_name,0,1)) }}</div>
        <h5 class="fw-700 mb-1">{{ $customer->full_name }}</h5>
        @if($customer->company)<p class="text-muted mb-3">{{ $customer->company }}</p>@endif
        <ul class="info-list text-start">
          <li><i class="bi bi-envelope"></i><a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></li>
          @if($customer->phone)<li><i class="bi bi-telephone"></i><a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a></li>@endif
          @if($customer->city)<li><i class="bi bi-geo-alt"></i>{{ $customer->address ? $customer->address.', ' : '' }}{{ $customer->city }}</li>@endif
          @if($customer->fiscal_code)<li><i class="bi bi-card-text"></i>CF: {{ $customer->fiscal_code }}</li>@endif
          @if($customer->vat_number)<li><i class="bi bi-building"></i>P.IVA: {{ $customer->vat_number }}</li>@endif
        </ul>
        @if($customer->notes)<div class="note-box mt-3"><i class="bi bi-sticky-fill me-1 text-warning"></i>{{ $customer->notes }}</div>@endif
        <div class="mt-3 text-muted small">Cliente dal {{ $customer->created_at->format('d/m/Y') }}</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-8">
    <div class="card-avr">
      <div class="card-header-avr d-flex justify-content-between align-items-center">
        <span><i class="bi bi-collection me-2"></i>Abbonamenti</span>
        <a href="{{ route('subscriptions.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-avr"><i class="bi bi-plus me-1"></i>Aggiungi</a>
      </div>
      @if($customer->subscriptions->isNotEmpty())
      <div class="p-3">
        @foreach($customer->subscriptions as $sub)
        <div class="sub-card sub-{{ $sub->expiry_class }} mb-3">
          <div class="sub-card-header">
            <div><span class="fw-600">{{ $sub->licenseType->name }}</span><span class="text-muted ms-2">× {{ $sub->quantity }}</span></div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge-expiry badge-{{ $sub->expiry_class }}">
                @if($sub->status==='cancelled') Annullato
                @elseif($sub->status==='expired') Scaduto
                @elseif($sub->expiry_class==='critical') Critico
                @elseif($sub->expiry_class==='warning') Attenzione
                @elseif($sub->expiry_class==='soon') Presto
                @else Attivo @endif
              </span>
              <a href="{{ route('subscriptions.edit', $sub) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
            </div>
          </div>
          <div class="sub-card-body">
            <div class="row g-2">
              <div class="col-6 col-md-3"><div class="sub-info-label">Inizio</div><div class="sub-info-value">{{ $sub->start_date->format('d/m/Y') }}</div></div>
              <div class="col-6 col-md-3"><div class="sub-info-label">Scadenza</div><div class="sub-info-value fw-600">{{ $sub->end_date->format('d/m/Y') }}</div></div>
              <div class="col-6 col-md-3"><div class="sub-info-label">Giorni rimasti</div><div class="sub-info-value">{{ $sub->days_to_expiry }}</div></div>
              <div class="col-6 col-md-3"><div class="sub-info-label">Prezzo</div><div class="sub-info-value fw-600">€{{ number_format($sub->effective_price,2,',','.') }}</div></div>
            </div>
            @if($sub->notes)<div class="note-box mt-2"><i class="bi bi-sticky me-1"></i>{{ $sub->notes }}</div>@endif
            <div class="mt-2 d-flex gap-2">
              <form method="POST" action="{{ route('subscriptions.remind', $sub) }}">@csrf
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-envelope me-1"></i>Invia Promemoria</button>
              </form>
              <form method="POST" action="{{ route('subscriptions.destroy', $sub) }}" onsubmit="return confirm('Annullare questo abbonamento?')">@csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i>Annulla</button>
              </form>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="text-center py-5 text-muted">
        <i class="bi bi-collection fs-1"></i><p class="mt-2">Nessun abbonamento</p>
        <a href="{{ route('subscriptions.create') }}" class="btn btn-avr btn-sm"><i class="bi bi-plus me-1"></i>Aggiungi</a>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection
