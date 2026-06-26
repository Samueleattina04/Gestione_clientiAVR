@extends('layouts.app')
@section('title','Nuovo Cliente')
@section('page_title')<i class="bi bi-person-plus-fill me-2 text-avr"></i> Nuovo Cliente@endsection
@section('content')
<form method="POST" action="{{ route('customers.store') }}">
@csrf
<div class="row g-4">
  <div class="col-12 col-xl-6">
    <div class="card-avr">
      <div class="card-header-avr"><i class="bi bi-person me-2"></i> Dati Anagrafici</div>
      <div class="p-4">
        <div class="row g-3">
          <div class="col-6"><label class="form-label required-label">Nome</label><input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required></div>
          <div class="col-6"><label class="form-label required-label">Cognome</label><input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required></div>
          <div class="col-12"><label class="form-label required-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
          <div class="col-6"><label class="form-label">Telefono</label><input type="tel" name="phone" class="form-control" value="{{ old('phone') }}"></div>
          <div class="col-6"><label class="form-label">Città</label><input type="text" name="city" class="form-control" value="{{ old('city') }}"></div>
          <div class="col-12"><label class="form-label">Azienda</label><input type="text" name="company" class="form-control" value="{{ old('company') }}"></div>
          <div class="col-6"><label class="form-label">Codice Fiscale</label><input type="text" name="fiscal_code" class="form-control" value="{{ old('fiscal_code') }}" maxlength="16"></div>
          <div class="col-6"><label class="form-label">P.IVA</label><input type="text" name="vat_number" class="form-control" value="{{ old('vat_number') }}"></div>
          <div class="col-12"><label class="form-label">Indirizzo</label><input type="text" name="address" class="form-control" value="{{ old('address') }}"></div>
          <div class="col-12"><label class="form-label">Note</label><textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-6">
    <div class="card-avr">
      <div class="card-header-avr"><i class="bi bi-collection me-2"></i> Abbonamento Iniziale <span class="badge bg-secondary ms-2">Opzionale</span></div>
      <div class="p-4">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Licenza</label>
            <select name="license_type_id" id="licenseSelect" class="form-select">
              <option value="">— Seleziona licenza —</option>
              @foreach($licenses as $lic)
              <option value="{{ $lic->id }}" data-pm="{{ $lic->price_monthly }}" data-py="{{ $lic->price_yearly }}">{{ $lic->name }} — €{{ number_format($lic->price_yearly,2,',','.') }}/anno</option>
              @endforeach
            </select>
          </div>
          <div class="col-4"><label class="form-label">Quantità</label><input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1"></div>
          <div class="col-8"><label class="form-label">Ciclo</label><select name="billing_cycle" id="billingCycle" class="form-select"><option value="yearly">Annuale</option><option value="monthly">Mensile</option></select></div>
          <div class="col-6"><label class="form-label">Data Inizio</label><input type="date" name="start_date" id="startDate" class="form-control"></div>
          <div class="col-6"><label class="form-label">Data Scadenza</label><input type="date" name="end_date" id="endDate" class="form-control"></div>
          <div class="col-12">
            <label class="form-label">Prezzo Personalizzato (€)</label>
            <div class="input-group"><span class="input-group-text">€</span><input type="number" name="custom_price" class="form-control" step="0.01" placeholder="Lascia vuoto per prezzo di listino"></div>
            <div class="form-text" id="priceHint">Seleziona una licenza per vedere il prezzo</div>
          </div>
          <div class="col-12"><label class="form-label">Note Abbonamento</label><textarea name="sub_notes" class="form-control" rows="2"></textarea></div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="d-flex gap-2 mt-4">
  <button type="submit" class="btn btn-avr px-4"><i class="bi bi-check-lg me-2"></i>Salva Cliente</button>
  <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary px-4">Annulla</a>
</div>
</form>
@endsection
@push('scripts')
<script src="{{ asset('js/subscription-form.js') }}"></script>
@endpush
