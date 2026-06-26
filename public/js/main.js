document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  if (toggle && sidebar) {
    toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
    // Chiude sidebar cliccando fuori su mobile
    document.addEventListener('click', e => {
      if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
        if (!sidebar.contains(e.target) && !toggle.contains(e.target)) sidebar.classList.remove('open');
      }
    });
    // Chiude sidebar cliccando su un link di navigazione su mobile
    sidebar.querySelectorAll('.nav-link').forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 768) sidebar.classList.remove('open');
      });
    });
  }
  document.querySelectorAll('.alert').forEach(a => setTimeout(() => { try { bootstrap.Alert.getOrCreateInstance(a).close(); } catch(e){} }, 5000));
  document.querySelectorAll('.kpi-value').forEach(el => {
    const target = parseInt(el.textContent.replace(/[^0-9]/g,''), 10);
    if (isNaN(target) || target === 0) return;
    let cur = 0; const step = Math.max(1, Math.ceil(target/30));
    const t = setInterval(() => { cur = Math.min(cur + step, target); el.textContent = cur; if(cur>=target) clearInterval(t); }, 20);
  });
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
});
