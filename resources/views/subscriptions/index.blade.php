@extends('layouts.app')
@section('title','Abbonamenti')
@section('page_title')<i class="bi bi-collection-fill me-2 text-avr"></i> Abbonamenti@endsection
@section('topbar_actions')
  <a href="{{ route('subscriptions.create') }}" class="btn btn-avr btn-sm"><i class="bi bi-plus-circle-fill me-1"></i>Nuovo</a>
@endsection
@section('content')
<div class="card-avr">
  <div class="card-header-avr d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex gap-2 align-items-center flex-wrap">
      <span><i class="bi bi-list me-1"></i>Abbonamenti</span>
      @foreach([['active','Attivi'],['expired','Scaduti'],['cancelled','Annullati']] as [$s,$l])
      <a href="{{ route('subscriptions.index', ['status'=>$s,'q'=>$q]) }}" class="btn btn-sm {{ $status===$s ? 'btn-avr' : 'btn-outline-secondary' }}">{{ $l }}</a>
      @endforeach
    </div>
    <form class="d-flex gap-2" method="GET">
      <input type="hidden" name="status" value="{{ $status }}">
      <div class="input-group input-group-sm" style="width:250px">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Cerca cliente o licenza…" value="{{ $q }}">
        @if($q)<a href="{{ route('subscriptions.index',['status'=>$status]) }}" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>@endif
      </div>
      <a href="{{ route('subscriptions.export') }}" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-hover avr-table mb-0">
      <thead><tr><th>Cliente</th><th>Licenza</th><th>Qtà</th><th>Scadenza</th><th>Giorni</th><th>Prezzo</th><th>Stato</th><th>Azioni</th></tr></thead>
      <tbody>
        @forelse($subscriptions as $sub)
        <tr>
          <td>
            <a href="{{ route('customers.show', $sub->customer) }}" class="fw-600 text-dark text-decoration-none">{{ $sub->customer->full_name }}</a>
            @if($sub->customer->company)<div class="text-muted small">{{ $sub->customer->company }}</div>@endif
          </td>
          <td><span class="badge-license">{{ $sub->licenseType->name }}</span></td>
          <td>{{ $sub->quantity }}</td>
          <td class="fw-500">{{ $sub->end_date->format('d/m/Y') }}</td>
          <td><span class="{{ $sub->days_to_expiry < 0 ? 'text-danger' : ($sub->days_to_expiry <= 30 ? 'text-warning fw-600' : ($sub->days_to_expiry <= 90 ? 'text-info' : '')) }}">{{ $sub->days_to_expiry }}</span></td>
          <td>€{{ number_format($sub->effective_price,2,',','.') }}</td>
          <td><span class="badge-expiry badge-{{ $sub->expiry_class }}">
            @if($sub->status==='cancelled') Annullato
            @elseif($sub->status==='expired'||$sub->days_to_expiry<0) Scaduto
            @elseif($sub->expiry_class==='critical') Critico
            @elseif($sub->expiry_class==='warning') Attenzione
            @elseif($sub->expiry_class==='soon') Presto
            @else OK @endif
          </span></td>
          <td>
            <div class="d-flex gap-1">
              <a href="{{ route('subscriptions.edit', $sub) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
              <form method="POST" action="{{ route('subscriptions.remind', $sub) }}">@csrf
                <button type="submit" class="btn btn-sm btn-outline-primary" title="Invia promemoria"><i class="bi bi-envelope"></i></button>
              </form>
              <form method="POST" action="{{ route('subscriptions.destroy', $sub) }}" onsubmit="return confirm('Annullare questo abbonamento?')">@csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-collection fs-1"></i><p class="mt-2">Nessun abbonamento trovato</p></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($subscriptions->hasPages())
  <div class="d-flex justify-content-center p-3">{{ $subscriptions->links() }}</div>
  @endif
</div>
@endsection
