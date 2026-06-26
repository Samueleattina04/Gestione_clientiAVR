@extends('layouts.app')
@section('title','Modifica Cliente')
@section('page_title')<i class="bi bi-pencil-fill me-2 text-avr"></i> Modifica Cliente@endsection
@section('content')
<form method="POST" action="{{ route('customers.update', $customer) }}">
@csrf @method('PUT')
<div class="card-avr" style="max-width:700px">
  <div class="card-header-avr"><i class="bi bi-person me-2"></i> {{ $customer->full_name }}</div>
  <div class="p-4">
    <div class="row g-3">
      <div class="col-6"><label class="form-label required-label">Nome</label><input type="text" name="first_name" class="form-control" value="{{ old('first_name',$customer->first_name) }}" required></div>
      <div class="col-6"><label class="form-label required-label">Cognome</label><input type="text" name="last_name" class="form-control" value="{{ old('last_name',$customer->last_name) }}" required></div>
      <div class="col-12"><label class="form-label required-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email',$customer->email) }}" required></div>
      <div class="col-6"><label class="form-label">Telefono</label><input type="tel" name="phone" class="form-control" value="{{ old('phone',$customer->phone) }}"></div>
      <div class="col-6"><label class="form-label">Città</label><input type="text" name="city" class="form-control" value="{{ old('city',$customer->city) }}"></div>
      <div class="col-12"><label class="form-label">Azienda</label><input type="text" name="company" class="form-control" value="{{ old('company',$customer->company) }}"></div>
      <div class="col-6"><label class="form-label">Codice Fiscale</label><input type="text" name="fiscal_code" class="form-control" value="{{ old('fiscal_code',$customer->fiscal_code) }}" maxlength="16"></div>
      <div class="col-6"><label class="form-label">P.IVA</label><input type="text" name="vat_number" class="form-control" value="{{ old('vat_number',$customer->vat_number) }}"></div>
      <div class="col-12"><label class="form-label">Indirizzo</label><input type="text" name="address" class="form-control" value="{{ old('address',$customer->address) }}"></div>
      <div class="col-12"><label class="form-label">Note</label><textarea name="notes" class="form-control" rows="3">{{ old('notes',$customer->notes) }}</textarea></div>
    </div>
  </div>
</div>
<div class="d-flex gap-2 mt-4">
  <button type="submit" class="btn btn-avr px-4"><i class="bi bi-check-lg me-2"></i>Salva Modifiche</button>
  <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary px-4">Annulla</a>
</div>
</form>
@endsection
