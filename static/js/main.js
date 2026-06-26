// A.V.R. Informatica — Gestionale JS

document.addEventListener('DOMContentLoaded', function () {

  // Sidebar toggle (mobile)
  const toggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');

  if (toggle && sidebar) {
    toggle.addEventListener('click', function () {
      sidebar.classList.toggle('open');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function (e) {
      if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
        if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
          sidebar.classList.remove('open');
        }
      }
    });
  }

  // Auto-dismiss alerts after 5 seconds
  document.querySelectorAll('.alert').forEach(function (alert) {
    setTimeout(function () {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      if (bsAlert) bsAlert.close();
    }, 5000);
  });

  // Confirm delete buttons
  document.querySelectorAll('[data-confirm]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      if (!confirm(this.dataset.confirm)) e.preventDefault();
    });
  });

  // Animate numbers in KPI cards
  document.querySelectorAll('.kpi-value').forEach(function (el) {
    const target = parseInt(el.textContent.replace(/[^0-9]/g, ''), 10);
    if (isNaN(target) || target === 0) return;
    let current = 0;
    const step = Math.max(1, Math.ceil(target / 30));
    const timer = setInterval(function () {
      current = Math.min(current + step, target);
      el.textContent = current;
      if (current >= target) clearInterval(timer);
    }, 20);
  });

  // Highlight expiring rows
  document.querySelectorAll('.avr-table tbody tr').forEach(function (row) {
    const badge = row.querySelector('.badge-critical');
    if (badge) row.style.background = 'rgba(200,16,46,.03)';
  });

  // Tooltip init
  document.querySelectorAll('[title]').forEach(function (el) {
    new bootstrap.Tooltip(el, { trigger: 'hover', placement: 'top' });
  });
});
