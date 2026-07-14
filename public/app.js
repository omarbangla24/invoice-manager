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

/* ===== Modals ===== */
(() => {
  const overlays = document.querySelectorAll('.modal-overlay');
  if (!overlays.length) return;

  const close = () => {
    overlays.forEach((m) => { m.hidden = true; });
    document.body.style.overflow = '';
  };
  const open = (id) => {
    const m = document.getElementById(id);
    if (!m) return;
    m.hidden = false;
    document.body.style.overflow = 'hidden';
    const focusable = m.querySelector('input, select, textarea, button');
    if (focusable) focusable.focus();
  };

  document.querySelectorAll('[data-modal-open]').forEach((btn) => {
    btn.addEventListener('click', () => open(btn.getAttribute('data-modal-open')));
  });
  document.querySelectorAll('[data-modal-close]').forEach((btn) => {
    btn.addEventListener('click', close);
  });
  overlays.forEach((m) => {
    m.addEventListener('click', (event) => { if (event.target === m) close(); });
  });
  window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') close();
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

/* ===== Load More Pagination ===== */
(() => {
  document.addEventListener('click', async (event) => {
    const btn = event.target.closest('.load-more-btn');
    if (!btn) return;

    event.preventDefault();
    const nextUrl = btn.dataset.nextUrl;
    if (!nextUrl) return;

    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Loading...';

    try {
      const response = await fetch(nextUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      });

      if (!response.ok) throw new Error('Failed to load more');

      const html = await response.text();
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');

      const newItems = doc.querySelectorAll('.job-row, table tbody tr');
      const container = document.querySelector('.jobs-list, table tbody');

      if (container && newItems.length > 0) {
        newItems.forEach((item) => {
          const clone = item.cloneNode(true);
          container.appendChild(clone);
        });

        if (typeof window.bindJobRowEvents === 'function') {
          window.bindJobRowEvents();
        }

        const newBtn = doc.querySelector('.load-more-btn');
        if (newBtn) {
          btn.dataset.nextUrl = newBtn.dataset.nextUrl;
          btn.dataset.currentPage = newBtn.dataset.currentPage;
          btn.disabled = false;
          btn.textContent = 'Load More';
        } else {
          const complete = doc.querySelector('.load-more-complete');
          if (complete) {
            btn.outerHTML = complete.outerHTML;
          } else {
            btn.remove();
          }
        }

        const url = new URL(nextUrl);
        window.history.replaceState({}, '', url.pathname + url.search);
      }
    } catch (error) {
      console.error('Load more failed:', error);
      btn.disabled = false;
      btn.textContent = originalText;
      alert('Failed to load more items. Please try again.');
    }
  });
})();
