@extends('layouts.app')
@section('title','Nuovo Abbonamento')
@section('page_title')<i class="bi bi-plus-circle-fill me-2 text-avr"></i> Nuovo Abbonamento@endsection
@section('content')
<form method="POST" action="{{ route('subscriptions.store') }}">@csrf
<div class="card-avr" style="max-width:750px">
  <div class="card-header-avr"><i class="bi bi-collection me-2"></i> Dati Abbonamento</div>
  <div class="p-4">
    <div class="row g-3">
      <div class="col-12"><label class="form-label required-label">Cliente</label>
        <select name="customer_id" class="form-select" required>
          <option value="">— Seleziona cliente —</option>
          @foreach($customers as $c)<option value="{{ $c->id }}" {{ $preCustomer==$c->id ? 'selected' : '' }}>{{ $c->full_name }}{{ $c->company ? ' — '.$c->company : '' }}</option>@endforeach
        </select></div>
      <div class="col-12"><label class="form-label required-label">Licenza</label>
        <select name="license_type_id" id="licenseSelect" class="form-select" required>
          <option value="">— Seleziona licenza —</option>
          @foreach($licenses as $lic)<option value="{{ $lic->id }}" data-pm="{{ $lic->price_monthly }}" data-py="{{ $lic->price_yearly }}">{{ $lic->name }}</option>@endforeach
        </select></div>
      <div class="col-4"><label class="form-label required-label">Quantità</label><input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" required></div>
      <div class="col-8"><label class="form-label">Ciclo</label><select name="billing_cycle" id="billingCycle" class="form-select"><option value="yearly">Annuale</option><option value="monthly">Mensile</option></select></div>
      <div class="col-6"><label class="form-label required-label">Data Inizio</label><input type="date" name="start_date" id="startDate" class="form-control" required></div>
      <div class="col-6"><label class="form-label required-label">Data Scadenza</label><input type="date" name="end_date" id="endDate" class="form-control" required></div>
      <div class="col-12"><label class="form-label">Prezzo Personalizzato (€)</label><div class="input-group"><span class="input-group-text">€</span><input type="number" name="custom_price" class="form-control" step="0.01" placeholder="Lascia vuoto per prezzo di listino"></div><div class="form-text" id="priceHint">Seleziona una licenza</div></div>
      <div class="col-6"><label class="form-label">Tenant ID Microsoft</label><input type="text" name="microsoft_tenant_id" class="form-control" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"></div>
      <div class="col-6"><label class="form-label">Subscription ID Microsoft</label><input type="text" name="microsoft_subscription_id" class="form-control"></div>
      <div class="col-12"><label class="form-label">Note</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
    </div>
  </div>
</div>
<div class="d-flex gap-2 mt-4">
  <button type="submit" class="btn btn-avr px-4"><i class="bi bi-check-lg me-2"></i>Salva</button>
  <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-secondary px-4">Annulla</a>
</div>
</form>
@endsection
@push('scripts')<script src="{{ asset('js/subscription-form.js') }}"></script>@endpush
