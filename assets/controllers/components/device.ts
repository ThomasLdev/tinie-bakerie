type BreakpointName = 'xs' | 'sm' | 'md' | 'lg' | 'xl' | 'xxl';

interface Breakpoints {
  xs: number;
  sm: number;
  md: number;
  lg: number;
  xl: number;
  xxl: number;
}

interface DeviceOptions {
  breakpoints?: Partial<Breakpoints>;
}

export default class Device {
  private breakpoints: Breakpoints;
  private mediaQueries: Record<string, MediaQueryList>;
  public current: BreakpointName;

  constructor(options: DeviceOptions = {}) {
    this.breakpoints = {
      xs: 0,
      sm: 640,
      md: 768,
      lg: 1024,
      xl: 1280,
      xxl: 1536,
      ...options.breakpoints,
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

  detect(): BreakpointName {
    // Use matchMedia for more efficient detection
    const breakpointNames = Object.keys(this.breakpoints).reverse() as BreakpointName[];

    for (const name of breakpointNames) {
      if (name === 'xs' || this.mediaQueries[name]?.matches) {
        this.current = name;
        return name;
      }
    }

    this.current = 'xs';
    return 'xs';
  }

  private _setupListeners(): void {
    // Use matchMedia listeners instead of resize for better performance
    Object.values(this.mediaQueries).forEach(mq => {
      mq.addEventListener('change', () => this.detect());
    });
  }

  // Better device type detection
  get isTouchDevice(): boolean {
    return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
  }

  get isHoverCapable(): boolean {
    return window.matchMedia('(hover: hover)').matches;
  }

  get hasFinePointer(): boolean {
    return window.matchMedia('(pointer: fine)').matches;
  }

  get prefersReducedMotion(): boolean {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  get isDarkMode(): boolean {
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
  }

  isMobile(): boolean {
    return (this.current === 'xs' || this.current === 'sm') && this.isTouchDevice;
  }

  isTablet(): boolean {
    return this.current === 'md' && this.isTouchDevice;
  }

  isDesktop(): boolean {
    return ['lg', 'xl', 'xxl'].includes(this.current) || this.hasFinePointer;
  }

  is(breakpoint: BreakpointName): boolean {
    return this.current === breakpoint;
  }

  isAtLeast(breakpoint: BreakpointName): boolean {
    return window.innerWidth >= this.breakpoints[breakpoint];
  }

  get width(): number {
    return window.innerWidth;
  }

  get height(): number {
    return window.innerHeight;
  }

  destroy(): void {
    Object.values(this.mediaQueries).forEach(mq => {
      mq.removeEventListener('change', () => this.detect());
    });
  }
}
