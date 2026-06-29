@extends('layouts.app')
@section('title','Modifica Licenza')
@section('page_title')<i class="bi bi-pencil-fill me-2 text-avr"></i> Modifica Licenza@endsection
@section('content')
<form method="POST" action="{{ route('licenses.update', $license) }}">@csrf @method('PUT')
<div class="card-avr" style="max-width:600px">
  <div class="card-header-avr"><i class="bi bi-key me-2"></i> {{ $license->name }}</div>
  <div class="p-4">
    <div class="row g-3">
      <div class="col-12"><label class="form-label required-label">Nome Licenza</label><input type="text" name="name" class="form-control" value="{{ old('name',$license->name) }}" required></div>
      @include('licenses._category_field', ['currentCategory' => old('category', $license->category)])
      <div class="col-12"><label class="form-label">Descrizione</label><textarea name="description" class="form-control" rows="2">{{ old('description',$license->description) }}</textarea></div>
      <div class="col-6"><label class="form-label">Prezzo Mensile (€)</label><div class="input-group"><span class="input-group-text">€</span><input type="number" name="price_monthly" class="form-control" step="0.01" value="{{ old('price_monthly',$license->price_monthly) }}"></div></div>
      <div class="col-6"><label class="form-label">Prezzo Annuale (€)</label><div class="input-group"><span class="input-group-text">€</span><input type="number" name="price_yearly" class="form-control" step="0.01" value="{{ old('price_yearly',$license->price_yearly) }}"></div></div>
      <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ $license->is_active ? 'checked' : '' }}><label class="form-check-label" for="isActive">Licenza attiva</label></div></div>
    </div>
  </div>
</div>
<div class="d-flex gap-2 mt-4">
  <button type="submit" class="btn btn-avr px-4"><i class="bi bi-check-lg me-2"></i>Salva Modifiche</button>
  <a href="{{ route('licenses.index') }}" class="btn btn-outline-secondary px-4">Annulla</a>
</div>
</form>
@endsection
