@extends('layouts.app')
@section('title','Scadenze')
@section('page_title')<i class="bi bi-clock-history me-2 text-avr"></i> Report Scadenze@endsection
@section('topbar_actions')
  <a href="{{ route('subscriptions.export') }}" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
@endsection
@section('content')
<div class="d-flex gap-2 mb-4 flex-wrap">
  @foreach([[30,'30 giorni'],[60,'60 giorni'],[90,'90 giorni'],[180,'6 mesi'],[365,'1 anno']] as [$d,$l])
  <a href="{{ route('reports.expiring',['days'=>$d]) }}" class="btn btn-sm {{ $days==$d ? 'btn-avr' : 'btn-outline-secondary' }}">{{ $l }}</a>
  @endforeach
</div>
@if($subs->isNotEmpty())
@php $critical=$subs->where('expiry_class','critical'); $warning=$subs->where('expiry_class','warning'); $soon=$subs->where('expiry_class','soon'); @endphp
<div class="row g-3 mb-4">
  <div class="col-4"><div class="kpi-card kpi-red text-center py-3"><div class="kpi-value">{{ $critical->count() }}</div><div class="kpi-label">Critici (&lt;30gg)</div></div></div>
  <div class="col-4"><div class="kpi-card kpi-orange text-center py-3"><div class="kpi-value">{{ $warning->count() }}</div><div class="kpi-label">Attenzione (&lt;90gg)</div></div></div>
  <div class="col-4"><div class="kpi-card kpi-blue text-center py-3"><div class="kpi-value">{{ $soon->count() }}</div><div class="kpi-label">Presto (&lt;180gg)</div></div></div>
</div>
<div class="card-avr">
  <div class="card-header-avr"><i class="bi bi-list me-2"></i>Scadenze entro {{ $days }} giorni <span class="badge bg-avr ms-2">{{ $subs->count() }}</span></div>
  <div class="table-responsive">
    <table class="table table-hover avr-table mb-0">
      <thead><tr><th>Cliente</th><th>Licenza</th><th>Scadenza</th><th>Giorni</th><th>Prezzo</th><th>Urgenza</th><th>Azioni</th></tr></thead>
      <tbody>
        @foreach($subs as $sub)
        <tr>
          <td><a href="{{ route('customers.show',$sub->customer) }}" class="fw-600 text-dark text-decoration-none">{{ $sub->customer->full_name }}</a><div class="text-muted small">{{ $sub->customer->email }}</div></td>
          <td><span class="badge-license">{{ $sub->licenseType->name }}</span><div class="text-muted small">×{{ $sub->quantity }}</div></td>
          <td class="fw-500">{{ $sub->end_date->format('d/m/Y') }}</td>
          <td><span class="fw-700 {{ $sub->days_to_expiry<=30?'text-danger':($sub->days_to_expiry<=90?'text-warning':'text-info') }}">{{ $sub->days_to_expiry }}</span></td>
          <td>€{{ number_format($sub->effective_price,2,',','.') }}</td>
          <td><span class="badge-expiry badge-{{ $sub->expiry_class }}">{{ $sub->expiry_class==='critical'?'🔴 Critico':($sub->expiry_class==='warning'?'🟠 Attenzione':'🟡 Presto') }}</span></td>
          <td><div class="d-flex gap-1">
            <form method="POST" action="{{ route('subscriptions.remind',$sub) }}">@csrf<button type="submit" class="btn btn-sm btn-avr"><i class="bi bi-envelope-fill me-1"></i>Invia</button></form>
            <a href="{{ route('subscriptions.edit',$sub) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
          </div></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@else
<div class="card-avr"><div class="p-5 text-center text-muted"><i class="bi bi-check-circle-fill fs-1 text-success"></i><h5 class="mt-3">Nessuna scadenza nei prossimi {{ $days }} giorni!</h5></div></div>
@endif
@endsection
