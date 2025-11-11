import { Controller } from '@hotwired/stimulus';

export default class extends Controller<HTMLElement> {
  static targets = ['collapsable', 'icon'];

  declare readonly collapsableTarget: HTMLElement;
  declare readonly iconTarget: HTMLElement;

  private hiddenClass = 'hidden';
  private rotateClass = 'rotate-180';
  private handleDocumentClickBound!: (event: MouseEvent) => void;

  connect(): void {
    this.handleDocumentClickBound = this.handleDocumentClick.bind(this);
  }

  displayCollapsable(): void {
    const isVisible = !this.collapsableTarget.classList.contains(this.hiddenClass);

    if (isVisible) {
      this.hideDropdown();
      return;
    }

    this.collapsableTarget.classList.remove(this.hiddenClass);
    this.iconTarget.classList.add(this.rotateClass);
    document.addEventListener('click', this.handleDocumentClickBound);
  }

  hideDropdown(): void {
    this.collapsableTarget.classList.add(this.hiddenClass);
    this.iconTarget.classList.remove(this.rotateClass);
    document.removeEventListener('click', this.handleDocumentClickBound);
  }

  handleDocumentClick(event: MouseEvent): void {
    if (this.collapsableTarget.classList.contains(this.hiddenClass)) return;

    const isOutsideDropdown = !this.element.contains(event.target as Node);

    if (isOutsideDropdown) {
      this.hideDropdown();
    }
  }

  disconnect(): void {
    document.removeEventListener('click', this.handleDocumentClickBound);
  }
}
