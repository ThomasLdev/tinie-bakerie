import { Controller } from '@hotwired/stimulus';
import Swiper from 'swiper/bundle';
import Device from './components/device.ts';

export default class extends Controller<HTMLElement> {
  static values = {
    mobileOnly: { type: Boolean, default: false },
  };

  declare readonly mobileOnlyValue: boolean;
  private swiper?: Swiper;

  connect(): void {
    const device = new Device();

    if (!device.isMobile() && this.mobileOnlyValue) {
      return;
    }

    this.swiper = new Swiper(this.element, {
      slidesPerView: 3,
      freeMode: true,
    });
  }

  disconnect(): void {
    if (this.swiper) {
      this.swiper.destroy(true, true);
      this.swiper = undefined;
    }
  }
}
