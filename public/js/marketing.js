(() => {
  'use strict';
  window.lucide?.createIcons({ attrs: { 'stroke-width': 1.8, 'aria-hidden': 'true' } });
  const menuButton = document.querySelector('[data-menu-toggle]');
  const menu = document.querySelector('#mobile-navigation');
  const closeMenu = () => {
    if (!menu || !menuButton) return;
    menu.hidden = true;
    menuButton.setAttribute('aria-expanded', 'false');
    menuButton.setAttribute('aria-label', menuButton.dataset.openLabel);
  };
  menuButton?.addEventListener('click', () => {
    menu.hidden = !menu.hidden;
    menuButton.setAttribute('aria-expanded', String(!menu.hidden));
    menuButton.setAttribute('aria-label', menu.hidden ? menuButton.dataset.openLabel : menuButton.dataset.closeLabel);
  });
  menu?.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
  matchMedia('(min-width: 851px)').addEventListener('change', event => { if (event.matches) closeMenu(); });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && menu && !menu.hidden) { closeMenu(); menuButton.focus(); }
  });
  const dialog = document.querySelector('#app-download');
  let opener;
  document.querySelectorAll('[data-app-open]').forEach(link => link.addEventListener('click', event => {
    if (!dialog?.showModal || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
    event.preventDefault();
    opener = link;
    dialog.showModal();
  }));
  dialog?.addEventListener('click', event => {
    const rect = dialog.getBoundingClientRect();
    if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) dialog.close();
  });
  dialog?.addEventListener('close', () => opener?.focus());
})();
