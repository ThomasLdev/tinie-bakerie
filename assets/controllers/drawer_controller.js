import { Controller } from '@hotwired/stimulus';

const FOCUSABLE_SELECTOR = 'a[href]:not([tabindex="-1"]), button:not([disabled]), [tabindex]:not([tabindex="-1"])';

export default class extends Controller {
    static targets = ['panel', 'backdrop', 'trigger', 'initialFocus'];

    #lastFocused = null;
    #onKeydown = (event) => {
        if (event.key === 'Escape' && document.body.dataset.drawer === 'open') {
            this.close();
        }
    };

    connect() {
        document.addEventListener('keydown', this.#onKeydown);
    }

    disconnect() {
        document.removeEventListener('keydown', this.#onKeydown);
        this.#unsetInert();
        delete document.body.dataset.drawer;
    }

    open(event) {
        event?.preventDefault();
        this.#lastFocused = document.activeElement;
        document.body.dataset.drawer = 'open';
        this.panelTarget.removeAttribute('inert');
        this.#setInert();
        if (this.hasTriggerTarget) {
            this.triggerTarget.setAttribute('aria-expanded', 'true');
        }
        const focusTarget = this.hasInitialFocusTarget
            ? this.initialFocusTarget
            : this.panelTarget.querySelector(FOCUSABLE_SELECTOR);
        focusTarget?.focus();
    }

    close() {
        delete document.body.dataset.drawer;
        this.#unsetInert();
        this.panelTarget.setAttribute('inert', '');
        if (this.hasTriggerTarget) {
            this.triggerTarget.setAttribute('aria-expanded', 'false');
        }
        this.#lastFocused?.focus();
        this.#lastFocused = null;
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
