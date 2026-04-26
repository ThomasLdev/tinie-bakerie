import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['panel', 'input'];

  #lastFocused = null;

  #onKeydown = event => {
    const isOpen = document.body.dataset.search === 'open';
    if (event.key === 'Escape' && isOpen) {
      this.close();
      return;
    }
    if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
      event.preventDefault();
      this.toggle();
      return;
    }
    if (event.key === '/' && !isOpen) {
      const tag = document.activeElement?.tagName;
      if (tag !== 'INPUT' && tag !== 'TEXTAREA') {
        event.preventDefault();
        this.open();
      }
    }
  };

  #onTriggerClick = event => {
    const trigger = event.target.closest('[data-search-open]');
    if (trigger) {
      event.preventDefault();
      this.open();
    }
  };

  connect() {
    document.addEventListener('keydown', this.#onKeydown);
    document.addEventListener('click', this.#onTriggerClick);
  }

  disconnect() {
    document.removeEventListener('keydown', this.#onKeydown);
    document.removeEventListener('click', this.#onTriggerClick);
    this.#unsetInert();
    delete document.body.dataset.search;
    this.#lastFocused = null;
  }

  open() {
    if (document.body.dataset.search === 'open') return;
    this.#lastFocused = document.activeElement;
    document.body.dataset.search = 'open';
    this.#setInert();
    requestAnimationFrame(() => {
      if (this.hasInputTarget) {
        this.inputTarget.focus();
        this.inputTarget.select();
      }
    });
  }

  close() {
    if (document.body.dataset.search !== 'open') return;
    delete document.body.dataset.search;
    this.#unsetInert();
    this.#lastFocused?.focus();
    this.#lastFocused = null;
  }

  toggle() {
    if (document.body.dataset.search === 'open') {
      this.close();
    } else {
      this.open();
    }
  }

  #setInert() {
    for (const sibling of document.body.children) {
      if (sibling === this.element) continue;
      if (sibling.contains(this.element)) continue;
      sibling.setAttribute('inert', '');
    }
  }

  #unsetInert() {
    for (const sibling of document.body.children) {
      sibling.removeAttribute('inert');
    }
  }
}
