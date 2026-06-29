@extends('layouts.app')
@section('title','Impostazioni Admin')
@section('page_title')<i class="bi bi-gear-fill me-2 text-avr"></i> Impostazioni Amministratore@endsection
@section('content')
<div class="row g-4">

  {{-- Profilo --}}
  <div class="col-12 col-lg-6">
    <div class="card-avr">
      <div class="card-header-avr"><i class="bi bi-person-fill me-2"></i> Profilo</div>
      <div class="p-4">
        <form method="POST" action="{{ route('admin.profile') }}">@csrf
          <div class="mb-3">
            <label class="form-label required-label">Nome e Cognome</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label required-label">Email account</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
            <div class="form-text">Usata per il login e per ricevere i promemoria di reset password.</div>
          </div>
          <button type="submit" class="btn btn-avr"><i class="bi bi-check-lg me-2"></i>Salva Profilo</button>
        </form>
      </div>
    </div>
  </div>

  {{-- Cambio password --}}
  <div class="col-12 col-lg-6">
    <div class="card-avr">
      <div class="card-header-avr"><i class="bi bi-lock-fill me-2"></i> Cambia Password</div>
      <div class="p-4">
        <form method="POST" action="{{ route('admin.password') }}">@csrf
          <div class="mb-3">
            <label class="form-label required-label">Password attuale</label>
            <input type="password" name="current_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label required-label">Nuova password</label>
            <input type="password" name="password" class="form-control" required minlength="8">
            <div class="form-text">Minimo 8 caratteri.</div>
          </div>
          <div class="mb-3">
            <label class="form-label required-label">Conferma nuova password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-avr"><i class="bi bi-lock me-2"></i>Cambia Password</button>
        </form>
      </div>
    </div>
  </div>

  {{-- Configurazione Email SMTP --}}
  <div class="col-12">
    <div class="card-avr">
      <div class="card-header-avr"><i class="bi bi-envelope-fill me-2"></i> Configurazione Email SMTP</div>
      <div class="p-4">

        {{-- Preset rapidi --}}
        <div class="mb-4">
          <p class="fw-600 mb-2">Configurazione rapida:</p>
          <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setPreset('outlook')">
              <i class="bi bi-microsoft me-1"></i>Outlook / Microsoft 365
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setPreset('gmail')">
              <i class="bi bi-google me-1"></i>Gmail
            </button>
          </div>
        </div>

        <form method="POST" action="{{ route('admin.smtp') }}">@csrf
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label required-label">Server SMTP (Host)</label>
              <input type="text" name="mail_host" id="mail_host" class="form-control" value="{{ old('mail_host', $smtp['host']) }}" required placeholder="smtp.office365.com">
            </div>
            <div class="col-6 col-md-2">
              <label class="form-label required-label">Porta</label>
              <input type="number" name="mail_port" id="mail_port" class="form-control" value="{{ old('mail_port', $smtp['port']) }}" required>
            </div>
            <div class="col-6 col-md-2">
              <label class="form-label required-label">Cifratura</label>
              <select name="mail_encryption" id="mail_encryption" class="form-select">
                @foreach(['tls'=>'TLS','ssl'=>'SSL','starttls'=>'STARTTLS'] as $val=>$lbl)
                  <option value="{{ $val }}" {{ old('mail_encryption', $smtp['encryption']) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label required-label">Email mittente (username SMTP)</label>
              <input type="email" name="mail_username" class="form-control" value="{{ old('mail_username', $smtp['username']) }}" required placeholder="info@avrinformatica.it">
              <div class="form-text">Questa sarà anche l'email del mittente e del rivenditore per i promemoria.</div>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Password SMTP</label>
              <input type="password" name="mail_password" class="form-control" placeholder="Lascia vuoto per non modificare" autocomplete="new-password">
              <div class="form-text">Per Outlook usa la password del tuo account Microsoft. Per Gmail usa l'App Password.</div>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label required-label">Nome mittente</label>
              <input type="text" name="mail_from_name" class="form-control" value="{{ old('mail_from_name', $smtp['from_name']) }}" required placeholder="A.V.R. Informatica">
            </div>
          </div>
          <div class="d-flex gap-2 mt-4 flex-wrap">
            <button type="submit" class="btn btn-avr"><i class="bi bi-save me-2"></i>Salva Configurazione</button>
            <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('testForm').submit()">
              <i class="bi bi-send me-2"></i>Invia Email di Test
            </button>
          </div>
        </form>

        <form id="testForm" method="POST" action="{{ route('admin.smtp.test') }}" class="d-none">@csrf</form>

        <div class="note-box mt-4">
          <i class="bi bi-info-circle-fill me-1 text-info"></i>
          <strong>Outlook / Microsoft 365:</strong> usa <code>smtp.office365.com</code>, porta <code>587</code>, cifratura <code>STARTTLS</code>.
          La password è quella del tuo account Microsoft (o una App Password se hai l'MFA attiva).
          <br><br>
          <strong>Gmail:</strong> usa <code>smtp.gmail.com</code>, porta <code>587</code>, cifratura <code>TLS</code>.
          Richiede un'App Password (non la password normale).
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
@push('scripts')
<script>
function setPreset(p) {
  const h = document.getElementById('mail_host');
  const po = document.getElementById('mail_port');
  const en = document.getElementById('mail_encryption');
  if (p === 'outlook') {
    h.value  = 'smtp.office365.com';
    po.value = '587';
    en.value = 'starttls';
  } else {
    h.value  = 'smtp.gmail.com';
    po.value = '587';
    en.value = 'tls';
  }
}
</script>
@endpush
