document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-confirm-submit]').forEach((button) => {
    button.addEventListener('click', () => {
      const form = document.getElementById(button.dataset.confirmSubmit);
      if (form && window.confirm(button.dataset.confirmMessage || 'Are you sure?')) {
        form.submit();
      }
    });
  });
});
