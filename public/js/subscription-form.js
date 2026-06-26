document.addEventListener('DOMContentLoaded', function(){
  const licSel = document.getElementById('licenseSelect');
  const qty = document.getElementById('quantity');
  const cycle = document.getElementById('billingCycle');
  const sd = document.getElementById('startDate');
  const ed = document.getElementById('endDate');
  const hint = document.getElementById('priceHint');
  if(!licSel) return;
  const today = new Date();
  if(sd && !sd.value) sd.value = today.toISOString().split('T')[0];
  if(ed && !ed.value) { const ny = new Date(today); ny.setFullYear(ny.getFullYear()+1); ed.value = ny.toISOString().split('T')[0]; }
  function updatePrice(){
    const opt = licSel.options[licSel.selectedIndex];
    if(!opt||!opt.value){ if(hint) hint.textContent='Seleziona una licenza per vedere il prezzo'; return; }
    const pm=parseFloat(opt.dataset.pm||0), py=parseFloat(opt.dataset.py||0);
    const q=parseInt(qty?.value||1), c=cycle?.value||'yearly';
    const price = c==='monthly' ? pm*q : py*q;
    if(hint) hint.textContent=`Listino: €${price.toFixed(2)} ${c==='monthly'?'/ mese':'/ anno'}`;
  }
  function updateEndDate(){
    if(!sd||!ed) return;
    const s = new Date(sd.value);
    if(isNaN(s.getTime())) return;
    if(cycle?.value==='monthly') s.setMonth(s.getMonth()+1);
    else s.setFullYear(s.getFullYear()+1);
    ed.value = s.toISOString().split('T')[0];
  }
  licSel.addEventListener('change', updatePrice);
  qty?.addEventListener('input', updatePrice);
  cycle?.addEventListener('change', () => { updatePrice(); updateEndDate(); });
  sd?.addEventListener('change', updateEndDate);
});
