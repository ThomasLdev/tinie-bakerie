import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['value', 'quantity', 'servingsLabel', 'decreaseBtn', 'increaseBtn'];
  static values = {
    base: { type: Number, default: 4 },
    min: { type: Number, default: 1 },
    max: { type: Number, default: 24 },
  };

  #current = 0;

  connect() {
    this.#current = this.baseValue;
    this.#render();
  }

  decrease() {
    if (this.#current <= this.minValue) return;
    this.#current -= 1;
    this.#render();
  }

  increase() {
    if (this.#current >= this.maxValue) return;
    this.#current += 1;
    this.#render();
  }

  #render() {
    if (this.hasValueTarget) {
      this.valueTarget.textContent = String(this.#current);
    }

    if (this.hasServingsLabelTarget) {
      const el = this.servingsLabelTarget;
      const tpl = this.#current === 1 ? el.dataset.singular : el.dataset.plural;
      if (tpl) {
        el.textContent = tpl.replace('%count%', String(this.#current));
      }
    }

    const ratio = this.#current / this.baseValue;
    this.quantityTargets.forEach(el => {
      const base = parseFloat(el.dataset.baseQuantity);
      if (Number.isNaN(base)) return;
      el.textContent = this.#format(base * ratio);
    });

    if (this.hasDecreaseBtnTarget) {
      this.decreaseBtnTarget.disabled = this.#current <= this.minValue;
    }
    if (this.hasIncreaseBtnTarget) {
      this.increaseBtnTarget.disabled = this.#current >= this.maxValue;
    }
  }

  #format(n) {
    if (n >= 10) return String(Math.round(n));
    if (Number.isInteger(n)) return String(n);
    return (Math.round(n * 10) / 10).toString().replace('.', ',');
  }
}
