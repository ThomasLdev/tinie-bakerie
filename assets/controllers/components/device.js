export default class Device {
    constructor(options = {}) {
        this.breakpoints = {
            xs: 0,
            sm: 640,
            md: 768,
            lg: 1024,
            xl: 1280,
            xxl: 1536,
            ...options.breakpoints
        };

        // Create media queries for better performance
        this.mediaQueries = {};
        Object.entries(this.breakpoints).forEach(([name, width]) => {
            if (width > 0) {
                this.mediaQueries[name] = window.matchMedia(`(min-width: ${width}px)`);
            }
        });

        this.current = this.detect();
        this._setupListeners();
    }

    detect() {
        // Use matchMedia for more efficient detection
        const breakpointNames = Object.keys(this.breakpoints).reverse();

        for (const name of breakpointNames) {
            if (name === 'xs' || this.mediaQueries[name]?.matches) {
                this.current = name;
                return name;
            }
        }

        this.current = 'xs';
        return 'xs';
    }

    _setupListeners() {
        // Use matchMedia listeners instead of resize for better performance
        Object.values(this.mediaQueries).forEach(mq => {
            mq.addEventListener('change', () => this.detect());
        });
    }

    // Better device type detection
    get isTouchDevice() {
        return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    }

    get isHoverCapable() {
        return window.matchMedia('(hover: hover)').matches;
    }

    get hasFinePointer() {
        return window.matchMedia('(pointer: fine)').matches;
    }

    get prefersReducedMotion() {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    get isDarkMode() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    isMobile() {
        return (this.current === 'xs' || this.current === 'sm') && this.isTouchDevice;
    }

    isTablet() {
        return this.current === 'md' && this.isTouchDevice;
    }

    isDesktop() {
        return ['lg', 'xl', 'xxl'].includes(this.current) || this.hasFinePointer;
    }

    is(breakpoint) {
        return this.current === breakpoint;
    }

    isAtLeast(breakpoint) {
        return window.innerWidth >= this.breakpoints[breakpoint];
    }

    get width() {
        return window.innerWidth;
    }

    get height() {
        return window.innerHeight;
    }

    destroy() {
        Object.values(this.mediaQueries).forEach(mq => {
            mq.removeEventListener('change', () => this.detect());
        });
    }
}
