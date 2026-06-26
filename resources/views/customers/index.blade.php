@extends('layouts.app')
@section('title','Clienti')
@section('page_title')<i class="bi bi-people-fill me-2 text-avr"></i> Clienti@endsection
@section('topbar_actions')
  <a href="{{ route('customers.create') }}" class="btn btn-avr btn-sm"><i class="bi bi-person-plus-fill me-1"></i> Nuovo Cliente</a>
@endsection
@section('content')
<div class="card-avr">
  <div class="card-header-avr d-flex align-items-center justify-content-between flex-wrap gap-2">
    <span><i class="bi bi-list-ul me-2"></i> Lista Clienti <span class="badge bg-avr ms-1">{{ $customers->total() }}</span></span>
    <form class="d-flex gap-2" method="GET">
      <div class="input-group input-group-sm" style="width:280px">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Cerca nome, email, azienda…" value="{{ $q }}">
        @if($q)<a href="{{ route('customers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>@endif
      </div>
      <a href="{{ route('customers.import.form') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-upload me-1"></i>Importa</a>
      <a href="{{ route('customers.export') }}" class="btn btn-sm btn-outline-success"><i class="bi bi-download me-1"></i>Excel</a>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-hover avr-table mb-0">
      <thead><tr><th>#</th><th>Nome</th><th>Azienda</th><th>Email</th><th>Telefono</th><th>Abbonamenti</th><th>Azioni</th></tr></thead>
      <tbody>
        @forelse($customers as $c)
        <tr>
          <td class="text-muted small">{{ $c->id }}</td>
          <td>
            <a href="{{ route('customers.show', $c) }}" class="fw-600 text-dark text-decoration-none customer-name-link">{{ $c->full_name }}</a>
            @if($c->city)<div class="text-muted small"><i class="bi bi-geo-alt"></i> {{ $c->city }}</div>@endif
          </td>
          <td>{{ $c->company ?: '—' }}</td>
          <td><a href="mailto:{{ $c->email }}" class="text-decoration-none">{{ $c->email }}</a></td>
          <td>{{ $c->phone ?: '—' }}</td>
          <td>
            @if($c->active_subscriptions_count > 0)
              <span class="badge bg-success">{{ $c->active_subscriptions_count }} attiv{{ $c->active_subscriptions_count === 1 ? 'o' : 'i' }}</span>
            @else
              <span class="badge bg-secondary">Nessuno</span>
            @endif
          </td>
          <td>
            <div class="d-flex gap-1">
              <a href="{{ route('customers.show', $c) }}" class="btn btn-sm btn-outline-primary" title="Dettaglio"><i class="bi bi-eye"></i></a>
              <a href="{{ route('customers.edit', $c) }}" class="btn btn-sm btn-outline-secondary" title="Modifica"><i class="bi bi-pencil"></i></a>
              <form method="POST" action="{{ route('customers.destroy', $c) }}" onsubmit="return confirm('Archiviare {{ addslashes($c->full_name) }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Archivia"><i class="bi bi-archive"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center py-5 text-muted">
          <i class="bi bi-person-x fs-1"></i>
          <p class="mt-2">Nessun cliente trovato@if($q) per "{{ $q }}"@endif</p>
          <a href="{{ route('customers.create') }}" class="btn btn-avr btn-sm"><i class="bi bi-person-plus me-1"></i>Aggiungi Cliente</a>
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($customers->hasPages())
  <div class="d-flex justify-content-center p-3">{{ $customers->links() }}</div>
  @endif
</div>
@endsection
