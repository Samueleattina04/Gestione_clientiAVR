<div class="col-12">
  <label class="form-label required-label">Categoria</label>
  <div id="catMode" class="d-flex gap-2 align-items-start">
    <div class="flex-grow-1">
      <select name="category" id="catSelect" class="form-select" required>
        @foreach($categories as $cat)
          <option value="{{ $cat }}" {{ old('category', $currentCategory ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
        <option value="__new__">+ Inserisci nuova categoria…</option>
      </select>
      <input type="text" id="catNew" class="form-control mt-2 d-none"
             placeholder="Nome nuova categoria" value="{{ old('category') }}">
    </div>
    <button type="button" id="catBack" class="btn btn-outline-secondary d-none" title="Torna alla lista">
      <i class="bi bi-arrow-left"></i>
    </button>
  </div>
</div>
@push('scripts')
<script>
(function(){
  const sel = document.getElementById('catSelect');
  const inp = document.getElementById('catNew');
  const back = document.getElementById('catBack');
  sel.addEventListener('change', function(){
    if(this.value === '__new__'){
      sel.removeAttribute('name');
      inp.setAttribute('name','category');
      inp.classList.remove('d-none');
      inp.required = true;
      inp.focus();
      back.classList.remove('d-none');
    }
  });
  back.addEventListener('click', function(){
    sel.setAttribute('name','category');
    inp.removeAttribute('name');
    inp.classList.add('d-none');
    inp.required = false;
    inp.value = '';
    back.classList.add('d-none');
    sel.value = sel.options[0].value;
  });
  // Se old() aveva un valore non in lista, mostra il campo testo
  const oldVal = "{{ old('category', $currentCategory ?? '') }}";
  const opts = Array.from(sel.options).map(o => o.value);
  if(oldVal && !opts.includes(oldVal) && oldVal !== '__new__'){
    sel.removeAttribute('name');
    inp.setAttribute('name','category');
    inp.classList.remove('d-none');
    inp.required = true;
    inp.value = oldVal;
    back.classList.remove('d-none');
  }
})();
</script>
@endpush
