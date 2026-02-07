import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['collapsable', 'icon'];

  hiddenClass = 'hidden';
  rotateClass = 'rotate-180';
  handleDocumentClickBound;

  connect() {
    this.handleDocumentClickBound = this.handleDocumentClick.bind(this);
  }

  displayCollapsable() {
    const isVisible = !this.collapsableTarget.classList.contains(this.hiddenClass);

    if (isVisible) {
      this.hideDropdown();
      return;
    }

    this.collapsableTarget.classList.remove(this.hiddenClass);
    this.iconTarget.classList.add(this.rotateClass);
    document.addEventListener('click', this.handleDocumentClickBound);
  }

  hideDropdown() {
    this.collapsableTarget.classList.add(this.hiddenClass);
    this.iconTarget.classList.remove(this.rotateClass);
    document.removeEventListener('click', this.handleDocumentClickBound);
  }

  handleDocumentClick(event) {
    if (this.collapsableTarget.classList.contains(this.hiddenClass)) return;

    const isOutsideDropdown = !this.element.contains(event.target);

    if (isOutsideDropdown) {
      this.hideDropdown();
    }
  }

  disconnect() {
    document.removeEventListener('click', this.handleDocumentClickBound);
  }
}
