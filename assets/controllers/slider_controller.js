import { Controller } from '@hotwired/stimulus';
import Swiper from 'swiper/bundle';
import Device from './components/device.js';

export default class extends Controller {
  static values = {
    mobileOnly: { type: Boolean, default: false },
    slidesPerView: { type: Number, default: 1 },
    freeMode: { type: Boolean, default: false },
    pagination: { type: Boolean, default: false },
  };

  connect() {
    const device = new Device();

    if (!device.isMobile() && this.mobileOnlyValue) {
      return;
    }

    const options = {
      slidesPerView: this.slidesPerViewValue,
      freeMode: this.freeModeValue,
    };

    if (this.paginationValue) {
      options.pagination = {
        el: this.element.querySelector('.swiper-pagination'),
        clickable: true,
      };
    }

    this.swiper = new Swiper(this.element, options);
  }

  disconnect() {
    if (this.swiper) {
      this.swiper.destroy(true, true);
      this.swiper = undefined;
    }
  }
}
