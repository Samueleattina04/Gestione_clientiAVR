@extends('layouts.app')
@section('title','Importa Excel')
@section('page_title')<i class="bi bi-upload me-2 text-avr"></i> Importa Clienti da Excel@endsection
@section('content')
<div class="row g-4">
  <div class="col-12 col-xl-6">
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
          <button type="submit" class="btn btn-avr w-100"><i class="bi bi-upload me-2"></i>Importa Clienti</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-6">
    <div class="card-avr">
      <div class="card-header-avr"><i class="bi bi-info-circle me-2"></i> Formato File</div>
      <div class="p-4">
        <p>Colonne accettate nel file Excel:</p>
        <table class="table table-sm avr-table">
          <thead><tr><th>Campo</th><th>Nomi accettati</th><th>Obblig.</th></tr></thead>
          <tbody>
            <tr><td>Nome</td><td><code>nome</code>, <code>first name</code></td><td><span class="badge bg-danger">Sì</span></td></tr>
            <tr><td>Cognome</td><td><code>cognome</code>, <code>last name</code></td><td></td></tr>
            <tr><td>Email</td><td><code>email</code>, <code>mail</code></td><td><span class="badge bg-danger">Sì</span></td></tr>
            <tr><td>Azienda</td><td><code>azienda</code>, <code>company</code></td><td></td></tr>
            <tr><td>Telefono</td><td><code>telefono</code>, <code>phone</code></td><td></td></tr>
            <tr><td>Città</td><td><code>città</code>, <code>city</code></td><td></td></tr>
          </tbody>
        </table>
        <div class="note-box"><i class="bi bi-lightbulb-fill me-1 text-warning"></i>Compatibile con l'export del Microsoft 365 Admin Center. I clienti già presenti (stessa email) vengono saltati.</div>
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
