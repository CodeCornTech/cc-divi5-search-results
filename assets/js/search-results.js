(() => {
  'use strict';

  const controllers = new WeakMap();
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  const findModule = (scope, instanceId) => {
    const modules = scope.querySelectorAll('.cc-d5sr__inner[data-cc-d5sr-instance]');

    return Array.from(modules).find((module) => module.dataset.ccD5srInstance === instanceId) || null;
  };

  const focusResults = (module) => {
    const results = module.querySelector('.cc-d5sr__results, .cc-d5sr__empty');

    module.scrollIntoView({
      behavior: reducedMotion.matches ? 'auto' : 'smooth',
      block: 'start',
    });

    if (results instanceof HTMLElement) {
      results.focus({ preventScroll: true });
    }
  };

  const loadPage = async (module, url) => {
    const instanceId = module.dataset.ccD5srInstance || '';

    if (!instanceId) {
      window.location.assign(url);
      return;
    }

    const previousController = controllers.get(module);
    if (previousController) {
      previousController.abort();
    }

    const controller = new AbortController();
    controllers.set(module, controller);

    module.classList.add('cc-d5sr--loading');
    module.setAttribute('aria-busy', 'true');

    try {
      const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CC-D5SR': 'pagination',
        },
        signal: controller.signal,
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const html = await response.text();
      const parsed = new DOMParser().parseFromString(html, 'text/html');
      const replacement = findModule(parsed, instanceId);

      if (!(replacement instanceof HTMLElement)) {
        throw new Error('CC Search Results module not found in response');
      }

      const imported = document.importNode(replacement, true);
      module.replaceWith(imported);
      window.history.pushState({ ccD5srPagination: true }, '', url);
      focusResults(imported);

      document.dispatchEvent(new CustomEvent('cc-d5sr:page-loaded', {
        detail: {
          instanceId,
          url,
        },
      }));
    } catch (error) {
      if (error instanceof DOMException && error.name === 'AbortError') {
        return;
      }

      window.location.assign(url);
    } finally {
      module.classList.remove('cc-d5sr--loading');
      module.removeAttribute('aria-busy');
      controllers.delete(module);
    }
  };

  document.addEventListener('click', (event) => {
    const target = event.target;

    if (!(target instanceof Element)) {
      return;
    }

    const link = target.closest('.cc-d5sr__pagination a');
    if (!(link instanceof HTMLAnchorElement)) {
      return;
    }

    const module = link.closest('.cc-d5sr__inner[data-cc-d5sr-ajax="on"]');
    if (!(module instanceof HTMLElement)) {
      return;
    }

    if (
      event.defaultPrevented
      || event.button !== 0
      || event.metaKey
      || event.ctrlKey
      || event.shiftKey
      || event.altKey
      || link.target === '_blank'
      || link.origin !== window.location.origin
    ) {
      return;
    }

    event.preventDefault();
    loadPage(module, link.href);
  });

  if (document.querySelector('.cc-d5sr__inner[data-cc-d5sr-ajax="on"]')) {
    window.history.replaceState({
      ...window.history.state,
      ccD5srPagination: true,
    }, '', window.location.href);
  }

  window.addEventListener('popstate', () => {
    if (document.querySelector('.cc-d5sr__inner[data-cc-d5sr-ajax="on"]')) {
      window.location.reload();
    }
  });
})();
