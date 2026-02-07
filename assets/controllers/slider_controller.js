import { Controller } from '@hotwired/stimulus';
import Swiper from 'swiper/bundle';
import Device from './components/device.js';

export default class extends Controller {
  static values = {
    mobileOnly: { type: Boolean, default: false },
  };

  connect() {
    const device = new Device();

    if (!device.isMobile() && this.mobileOnlyValue) {
      return;
    }

    this.swiper = new Swiper(this.element, {
      slidesPerView: 3,
      freeMode: true,
    });
  }

  disconnect() {
    if (this.swiper) {
      this.swiper.destroy(true, true);
      this.swiper = undefined;
    }
  }
}
