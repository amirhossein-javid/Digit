(() => {
  'use strict';
  const button = document.querySelector('[data-admin-menu]');
  const nav = document.querySelector('[data-admin-nav]');
  if (button && nav) {
    button.addEventListener('click', () => {
      const open = nav.classList.toggle('is-open');
      button.setAttribute('aria-expanded', String(open));
    });
  }

  const specs = document.querySelector('[data-specs]');
  const template = document.querySelector('#specRowTemplate');
  document.querySelector('[data-add-spec]')?.addEventListener('click', () => {
    if (specs && template && specs.children.length < 20) specs.append(template.content.cloneNode(true));
  });
  specs?.addEventListener('click', event => {
    const remove = event.target.closest('[data-remove-spec]');
    if (remove) remove.closest('.spec-row')?.remove();
  });
})();
