// Native Lucide attributes do not trigger the legacy font-icon converter.
window.lucide?.createIcons({ attrs: { 'stroke-width': 1.8, 'aria-hidden': 'true' } });

document.addEventListener('submit', (event) => {
  const control = event.submitter;
  if (control?.dataset.confirm && !window.confirm(control.dataset.confirm)) {
    event.preventDefault();
    return;
  }
  // Mark only an accepted, validated submission. Server responses remain authoritative.
  if (control?.classList.contains('portal-button') && !event.defaultPrevented) {
    control.setAttribute('aria-busy', 'true');
    setTimeout(() => { control.disabled = true; }, 0);
  }
});

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
  const input = document.getElementById(button.dataset.passwordToggle);
  if (!input) return;
  button.hidden = false;
  button.addEventListener('click', () => {
    const visible = input.type === 'password';
    input.type = visible ? 'text' : 'password';
    button.setAttribute('aria-pressed', String(visible));
    button.setAttribute('aria-label', visible ? button.dataset.hideLabel : button.dataset.showLabel);
  });
});

window.addEventListener('pageshow', () => {
  document.querySelectorAll('.portal-button[aria-busy]').forEach((button) => {
    button.removeAttribute('aria-busy');
    button.disabled = false;
  });
});

// Keep the selected page visible in the mobile navigation without moving the
// document itself or requiring a menu interaction.
document.fonts.ready.then(() => {
  const nav = document.querySelector('.portal-nav');
  const current = nav?.querySelector('[aria-current="page"]');
  if (!current || nav.scrollWidth <= nav.clientWidth) return;
  const bounds = nav.getBoundingClientRect();
  const selected = current.getBoundingClientRect();
  if (selected.right > bounds.right) nav.scrollLeft += selected.right - bounds.right;
  else if (selected.left < bounds.left) nav.scrollLeft -= bounds.left - selected.left;
});
