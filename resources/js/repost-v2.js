/* Repost v2 integration helpers for OgraFi.
   Existing app.js remains responsible for the template's core interactions. */
(() => {
  const init = () => {
    document.querySelectorAll('[data-dropdown-toggle]').forEach((trigger) => {
      if (trigger.dataset.ografiV2Ready === '1') return;
      trigger.dataset.ografiV2Ready = '1';
      trigger.addEventListener('click', () => {
        const expanded = trigger.getAttribute('aria-expanded') === 'true';
        trigger.setAttribute('aria-expanded', String(!expanded));
        const menu = trigger.parentElement?.querySelector('.dropdown-menu');
        menu?.classList.toggle('show', !expanded);
      });
    });
  };
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
  document.addEventListener('turbo:load', init);
})();
