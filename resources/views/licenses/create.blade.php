@extends('layouts.app')
@section('title','Nuova Licenza')
@section('page_title')<i class="bi bi-key me-2 text-avr"></i> Nuova Licenza@endsection
@section('content')
<form method="POST" action="{{ route('licenses.store') }}">@csrf
<div class="card-avr" style="max-width:600px">
  <div class="card-header-avr"><i class="bi bi-key me-2"></i> Dati Licenza</div>
  <div class="p-4">
    <div class="row g-3">
      <div class="col-12"><label class="form-label required-label">Nome Licenza</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
      @include('licenses._category_field', ['currentCategory' => old('category','Microsoft 365')])
      <div class="col-12"><label class="form-label">Descrizione</label><textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea></div>
      <div class="col-6"><label class="form-label">Prezzo Mensile (€)</label><div class="input-group"><span class="input-group-text">€</span><input type="number" name="price_monthly" class="form-control" step="0.01" value="{{ old('price_monthly','0.00') }}"></div></div>
      <div class="col-6"><label class="form-label">Prezzo Annuale (€)</label><div class="input-group"><span class="input-group-text">€</span><input type="number" name="price_yearly" class="form-control" step="0.01" value="{{ old('price_yearly','0.00') }}"></div></div>
    </div>
  </div>
</div>
<div class="d-flex gap-2 mt-4">
  <button type="submit" class="btn btn-avr px-4"><i class="bi bi-check-lg me-2"></i>Salva</button>
  <a href="{{ route('licenses.index') }}" class="btn btn-outline-secondary px-4">Annulla</a>
</div>
</form>
@endsection
