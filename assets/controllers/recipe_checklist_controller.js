import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['item', 'count', 'progress'];
  static values = {
    storageKey: String,
    total: { type: Number, default: 0 },
  };

  connect() {
    this.#restore();
    this.updateCount();
  }

  updateCount() {
    const count = this.itemTargets.filter(i => i.checked).length;

    if (this.hasCountTarget) {
      this.countTarget.textContent = String(count);
    }

    if (this.hasProgressTarget) {
      const total = this.totalValue || this.itemTargets.length;
      const ratio = total > 0 ? Math.round((count / total) * 100) : 0;
      this.progressTarget.style.setProperty('--progress', ratio);
      this.progressTarget.dataset.count = String(count);
      this.progressTarget.dataset.total = String(total);
    }

    this.#persist();
  }

  reset() {
    this.itemTargets.forEach(i => {
      i.checked = false;
    });
    this.updateCount();
  }

  #persist() {
    if (!this.storageKeyValue) return;
    try {
      const state = this.itemTargets.map(i => i.checked);
      localStorage.setItem(this.storageKeyValue, JSON.stringify(state));
    } catch {
      /* localStorage unavailable */
    }
  }

  #restore() {
    if (!this.storageKeyValue) return;
    try {
      const raw = localStorage.getItem(this.storageKeyValue);
      if (!raw) return;
      const state = JSON.parse(raw);
      if (!Array.isArray(state)) return;
      this.itemTargets.forEach((item, i) => {
        if (state[i] === true) item.checked = true;
      });
    } catch {
      /* corrupt localStorage entry */
    }
  }
}
