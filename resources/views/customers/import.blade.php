@extends('layouts.app')
@section('title','Importa Excel')
@section('page_title')<i class="bi bi-upload me-2 text-avr"></i> Importa Clienti da Excel@endsection
@section('content')
<div class="row g-4">

  {{-- Form upload --}}
  <div class="col-12 col-xl-5">
    <div class="card-avr">
      <div class="card-header-avr"><i class="bi bi-upload me-2"></i> Carica File Excel</div>
      <div class="p-4">
        <form method="POST" action="{{ route('customers.import') }}" enctype="multipart/form-data">
          @csrf
          <div class="upload-area mb-4" id="dropZone">
            <i class="bi bi-file-earmark-excel-fill fs-1 text-success"></i>
            <h5 class="mt-2">Trascina il file qui</h5>
            <p class="text-muted small">oppure clicca per selezionare</p>
            <input type="file" name="file" id="fileInput" accept=".xlsx,.xls" class="d-none" required>
            <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('fileInput').click()">
              <i class="bi bi-folder2-open me-1"></i>Sfoglia
            </button>
            <div id="fileName" class="mt-2 text-muted small"></div>
          </div>
          <button type="submit" class="btn btn-avr w-100"><i class="bi bi-upload me-2"></i>Importa</button>
        </form>
      </div>
    </div>

    <div class="card-avr mt-4">
      <div class="card-header-avr"><i class="bi bi-info-circle me-2"></i> Cosa fa l'import</div>
      <div class="p-4">
        <ul class="mb-0" style="font-size:14px;line-height:2">
          <li>Crea il cliente se non esiste (controlla l'email)</li>
          <li>Se il cliente esiste già, lo salta e passa all'abbonamento</li>
          <li>Se la licenza esiste già nel catalogo, la riusa</li>
          <li>Se la licenza <strong>non esiste</strong>, la crea automaticamente</li>
          <li>Crea l'abbonamento e lo collega al cliente</li>
          <li>Evita duplicati (stesso cliente + licenza + scadenza)</li>
          <li>Imposta lo stato <span class="badge bg-danger">scaduto</span> se la data è nel passato</li>
        </ul>
      </div>
    </div>
  </div>

  {{-- Formato file --}}
  <div class="col-12 col-xl-7">
    <div class="card-avr">
      <div class="card-header-avr"><i class="bi bi-table me-2"></i> Formato File Excel</div>
      <div class="p-4">

        <p class="fw-600 mb-2">Colonne cliente <span class="badge bg-danger ms-1">Email e Nome obbligatori</span></p>
        <table class="table table-sm avr-table mb-4">
          <thead><tr><th>Campo</th><th>Intestazioni accettate</th><th>Obblig.</th></tr></thead>
          <tbody>
            <tr><td>Nome</td><td><code>nome</code> <code>first name</code> <code>name</code></td><td><span class="badge bg-danger">Sì</span></td></tr>
            <tr><td>Cognome</td><td><code>cognome</code> <code>last name</code> <code>surname</code></td><td></td></tr>
            <tr><td>Email</td><td><code>email</code> <code>mail</code></td><td><span class="badge bg-danger">Sì</span></td></tr>
            <tr><td>Azienda</td><td><code>azienda</code> <code>company</code> <code>ragione sociale</code></td><td></td></tr>
            <tr><td>Telefono</td><td><code>telefono</code> <code>phone</code> <code>cellulare</code></td><td></td></tr>
            <tr><td>Città</td><td><code>citta</code> <code>city</code> <code>comune</code></td><td></td></tr>
            <tr><td>Codice Fiscale</td><td><code>codice fiscale</code> <code>cf</code></td><td></td></tr>
            <tr><td>Partita IVA</td><td><code>partita iva</code> <code>piva</code></td><td></td></tr>
            <tr><td>Indirizzo</td><td><code>indirizzo</code> <code>address</code></td><td></td></tr>
            <tr><td>Note</td><td><code>note</code> <code>notes</code></td><td></td></tr>
          </tbody>
        </table>

        <p class="fw-600 mb-2">Colonne abbonamento <span class="text-muted fw-normal" style="font-size:13px">(facoltative — se presenti vengono importate)</span></p>
        <table class="table table-sm avr-table mb-4">
          <thead><tr><th>Campo</th><th>Intestazioni accettate</th><th>Note</th></tr></thead>
          <tbody>
            <tr><td>Licenza</td><td><code>licenza</code> <code>license</code> <code>piano</code> <code>prodotto</code></td><td>Se non esiste viene creata</td></tr>
            <tr><td>Quantità</td><td><code>quantita</code> <code>quantity</code> <code>qty</code> <code>seats</code></td><td>Default: 1</td></tr>
            <tr><td>Data inizio</td><td><code>data inizio</code> <code>start_date</code> <code>inizio</code></td><td>Default: oggi</td></tr>
            <tr><td>Data scadenza</td><td><code>scadenza</code> <code>end_date</code> <code>data scadenza</code></td><td>Default: +1 mese/anno</td></tr>
            <tr><td>Ciclo fatturazione</td><td><code>ciclo</code> <code>billing_cycle</code> <code>fatturazione</code></td><td><code>mensile</code> o <code>annuale</code></td></tr>
            <tr><td>Prezzo</td><td><code>prezzo</code> <code>price</code> <code>importo</code></td><td>Prezzo personalizzato €</td></tr>
          </tbody>
        </table>

        <div class="note-box mb-3">
          <i class="bi bi-lightbulb-fill me-1 text-warning"></i>
          <strong>Esempio riga Excel:</strong><br>
          <span style="font-size:13px">
            Mario | Rossi | mario.rossi@email.it | Rossi SRL | 02 1234567 | Milano |
            Microsoft 365 Business Standard | 5 | 01/01/2025 | 31/12/2025 | annuale | 58.50
          </span>
        </div>

        <div class="note-box">
          <i class="bi bi-info-circle-fill me-1 text-info"></i>
          Le date possono essere nei formati <code>dd/mm/yyyy</code>, <code>yyyy-mm-dd</code> o come numero seriale Excel.
          Compatibile con l'export del Microsoft 365 Admin Center.
        </div>

      </div>
    </div>
  </div>

</div>
@endsection
@push('scripts')
<script>
const fi=document.getElementById('fileInput'),fn=document.getElementById('fileName'),dz=document.getElementById('dropZone');
fi.addEventListener('change',()=>{if(fi.files[0])fn.textContent='📄 '+fi.files[0].name;});
dz.addEventListener('dragover',e=>{e.preventDefault();dz.classList.add('drag-over');});
dz.addEventListener('dragleave',()=>dz.classList.remove('drag-over'));
dz.addEventListener('drop',e=>{e.preventDefault();dz.classList.remove('drag-over');const f=e.dataTransfer.files[0];if(f&&(f.name.endsWith('.xlsx')||f.name.endsWith('.xls'))){const dt=new DataTransfer();dt.items.add(f);fi.files=dt.files;fn.textContent='📄 '+f.name;}});
</script>
@endpush
