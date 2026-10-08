document.addEventListener('submit', (event) => {
  const control = event.submitter;
  if (control?.dataset.confirm && !window.confirm(control.dataset.confirm)) {
    event.preventDefault();
  }
});
