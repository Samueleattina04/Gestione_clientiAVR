@extends('layouts.app')
@section('title','Modifica Abbonamento')
@section('page_title')<i class="bi bi-pencil-fill me-2 text-avr"></i> Modifica Abbonamento@endsection
@section('content')
<form method="POST" action="{{ route('subscriptions.update', $subscription) }}">@csrf @method('PUT')
<div class="card-avr" style="max-width:750px">
  <div class="card-header-avr"><i class="bi bi-collection me-2"></i> Abbonamento #{{ $subscription->id }}</div>
  <div class="p-4">
    <div class="row g-3">
      <div class="col-12"><label class="form-label required-label">Cliente</label><select name="customer_id" class="form-select" required>@foreach($customers as $c)<option value="{{ $c->id }}" {{ $c->id==$subscription->customer_id ? 'selected' : '' }}>{{ $c->full_name }}{{ $c->company ? ' — '.$c->company : '' }}</option>@endforeach</select></div>
      <div class="col-12"><label class="form-label required-label">Licenza</label><select name="license_type_id" class="form-select" required>@foreach($licenses as $lic)<option value="{{ $lic->id }}" {{ $lic->id==$subscription->license_type_id ? 'selected' : '' }}>{{ $lic->name }}</option>@endforeach</select></div>
      <div class="col-4"><label class="form-label">Quantità</label><input type="number" name="quantity" class="form-control" value="{{ $subscription->quantity }}" min="1"></div>
      <div class="col-4"><label class="form-label">Ciclo</label><select name="billing_cycle" class="form-select"><option value="yearly" {{ $subscription->billing_cycle==='yearly'?'selected':'' }}>Annuale</option><option value="monthly" {{ $subscription->billing_cycle==='monthly'?'selected':'' }}>Mensile</option></select></div>
      <div class="col-4"><label class="form-label">Stato</label><select name="status" class="form-select"><option value="active" {{ $subscription->status==='active'?'selected':'' }}>Attivo</option><option value="expired" {{ $subscription->status==='expired'?'selected':'' }}>Scaduto</option><option value="cancelled" {{ $subscription->status==='cancelled'?'selected':'' }}>Annullato</option><option value="suspended" {{ $subscription->status==='suspended'?'selected':'' }}>Sospeso</option></select></div>
      <div class="col-6"><label class="form-label">Data Inizio</label><input type="date" name="start_date" class="form-control" value="{{ $subscription->start_date->format('Y-m-d') }}" required></div>
      <div class="col-6"><label class="form-label">Data Scadenza</label><input type="date" name="end_date" class="form-control" value="{{ $subscription->end_date->format('Y-m-d') }}" required></div>
      <div class="col-12"><label class="form-label">Prezzo Personalizzato (€)</label><div class="input-group"><span class="input-group-text">€</span><input type="number" name="custom_price" class="form-control" step="0.01" value="{{ $subscription->custom_price }}"></div></div>
      <div class="col-6"><label class="form-label">Tenant ID Microsoft</label><input type="text" name="microsoft_tenant_id" class="form-control" value="{{ $subscription->microsoft_tenant_id }}"></div>
      <div class="col-6"><label class="form-label">Subscription ID Microsoft</label><input type="text" name="microsoft_subscription_id" class="form-control" value="{{ $subscription->microsoft_subscription_id }}"></div>
      <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="auto_renew" id="ar" {{ $subscription->auto_renew?'checked':'' }}><label class="form-check-label" for="ar">Rinnovo automatico</label></div></div>
      <div class="col-12"><label class="form-label">Note</label><textarea name="notes" class="form-control" rows="2">{{ $subscription->notes }}</textarea></div>
    </div>
  </div>
</div>
<div class="d-flex gap-2 mt-4">
  <button type="submit" class="btn btn-avr px-4"><i class="bi bi-check-lg me-2"></i>Salva Modifiche</button>
  <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-secondary px-4">Annulla</a>
</div>
</form>
@endsection
