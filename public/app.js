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

(() => {
  const root = document.querySelector('[data-notif]');
  if (!root) return;

  const toggle = root.querySelector('[data-notif-toggle]');
  const panel = root.querySelector('[data-notif-panel]');
  const badge = root.querySelector('[data-notif-badge]');
  const list = root.querySelector('[data-notif-list]');

  const render = (data) => {
    const count = data.count || 0;
    if (count > 0) {
      badge.textContent = count > 99 ? '99+' : String(count);
      badge.hidden = false;
    } else {
      badge.hidden = true;
    }

    list.textContent = '';
    if (!data.items || data.items.length === 0) {
      const empty = document.createElement('div');
      empty.className = 'notif-empty';
      empty.textContent = 'No new notifications.';
      list.appendChild(empty);
      return;
    }

    data.items.forEach((item) => {
      const node = item.url ? document.createElement('a') : document.createElement('div');
      node.className = 'notif-item';
      if (item.url) node.href = item.url;

      const title = document.createElement('div');
      title.className = 'notif-item-title';
      title.textContent = item.title || '';
      node.appendChild(title);

      if (item.body) {
        const body = document.createElement('div');
        body.className = 'notif-item-body';
        body.textContent = item.body;
        node.appendChild(body);
      }

      const time = document.createElement('div');
      time.className = 'notif-item-time';
      time.textContent = item.time || '';
      node.appendChild(time);

      list.appendChild(node);
    });
  };

  const poll = () => {
    fetch('/notifications/feed', {
      headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
      credentials: 'same-origin',
    })
      .then((response) => (response.ok ? response.json() : null))
      .then((data) => { if (data) render(data); })
      .catch(() => {});
  };

  toggle.addEventListener('click', (event) => {
    event.stopPropagation();
    panel.hidden = !panel.hidden;
  });

  document.addEventListener('click', (event) => {
    if (!root.contains(event.target)) panel.hidden = true;
  });

  window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') panel.hidden = true;
  });

  poll();
  setInterval(poll, 15000);
})();
