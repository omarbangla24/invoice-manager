(() => {
  const shell = document.getElementById('appShell');
  if (!shell) return;

  const collapsed = localStorage.getItem('sidebar-collapsed') === '1';
  if (collapsed) document.body.classList.add('sidebar-collapsed');

  const openMobile = () => document.body.classList.add('sidebar-open');
  const closeMobile = () => document.body.classList.remove('sidebar-open');

  document.querySelectorAll('[data-sidebar-open]').forEach((button) => {
    button.addEventListener('click', openMobile);
  });

  document.querySelectorAll('[data-sidebar-close]').forEach((button) => {
    button.addEventListener('click', closeMobile);
  });

  document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      document.body.classList.toggle('sidebar-collapsed');
      localStorage.setItem('sidebar-collapsed', document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
    });
  });

  window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeMobile();
  });
})();
