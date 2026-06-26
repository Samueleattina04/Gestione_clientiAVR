@extends('layouts.app')
@section('title','Licenze')
@section('page_title')<i class="bi bi-key-fill me-2 text-avr"></i> Licenze Microsoft@endsection
@section('topbar_actions')
  <a href="{{ route('licenses.create') }}" class="btn btn-avr btn-sm"><i class="bi bi-plus me-1"></i>Nuova Licenza</a>
@endsection
@section('content')
@foreach($licenses as $category => $group)
<div class="card-avr mb-4">
  <div class="card-header-avr">
    <i class="bi bi-microsoft me-2"></i>{{ $category }}
    <span class="badge bg-avr ms-2">{{ $group->count() }}</span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover avr-table mb-0">
      <thead><tr><th>Nome Licenza</th><th>Descrizione</th><th>€/mese</th><th>€/anno</th><th>Stato</th><th>Azioni</th></tr></thead>
      <tbody>
        @foreach($group as $lic)
        <tr>
          <td class="fw-600">{{ $lic->name }}</td>
          <td class="text-muted small">{{ $lic->description ?: '—' }}</td>
          <td>€{{ number_format($lic->price_monthly,2,',','.') }}</td>
          <td class="fw-500">€{{ number_format($lic->price_yearly,2,',','.') }}</td>
          <td>@if($lic->is_active)<span class="badge bg-success">Attiva</span>@else<span class="badge bg-secondary">Disattiva</span>@endif</td>
          <td>
            <div class="d-flex gap-1">
              <a href="{{ route('licenses.edit', $lic) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
              <form method="POST" action="{{ route('licenses.destroy', $lic) }}" onsubmit="return confirm('Disattivare {{ addslashes($lic->name) }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-dash-circle"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endforeach
@endsection
