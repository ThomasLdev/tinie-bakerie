import { Application } from '@hotwired/stimulus';
import RecipePortionsController from '@assets/controllers/recipe_portions_controller.js';
import { afterEach, describe, expect, it } from 'vitest';

const IDENTIFIER = 'recipe-portions';

interface MountOptions {
  base?: number;
  min?: number;
  max?: number;
  withDecreaseBtn?: boolean;
  withIncreaseBtn?: boolean;
  withValueTarget?: boolean;
  withServingsLabel?: boolean;
  servingsSingular?: string;
  servingsPlural?: string;
  quantities?: { id: string; baseQuantity: string }[];
}

let app: Application;

async function mount(options: MountOptions = {}) {
  const {
    base = 4,
    min = 1,
    max = 24,
    withDecreaseBtn = true,
    withIncreaseBtn = true,
    withValueTarget = true,
    withServingsLabel = false,
    servingsSingular,
    servingsPlural,
    quantities = [],
  } = options;

  const decreaseBtn = withDecreaseBtn
    ? `<button data-test-id="portions-decrease" data-${IDENTIFIER}-target="decreaseBtn" data-action="click->${IDENTIFIER}#decrease">−</button>`
    : '';
  const increaseBtn = withIncreaseBtn
    ? `<button data-test-id="portions-increase" data-${IDENTIFIER}-target="increaseBtn" data-action="click->${IDENTIFIER}#increase">+</button>`
    : '';
  const valueTarget = withValueTarget
    ? `<span data-test-id="portions-value" data-${IDENTIFIER}-target="value">${base}</span>`
    : '';
  const servingsLabel = withServingsLabel
    ? `<span data-test-id="portions-servings"
            data-${IDENTIFIER}-target="servingsLabel"
            ${servingsSingular ? `data-singular="${servingsSingular}"` : ''}
            ${servingsPlural ? `data-plural="${servingsPlural}"` : ''}></span>`
    : '';
  const quantityTargets = quantities
    .map(
      q =>
        `<span data-test-id="${q.id}" data-${IDENTIFIER}-target="quantity" data-base-quantity="${q.baseQuantity}"></span>`,
    )
    .join('');

  document.body.innerHTML = `
    <div data-controller="${IDENTIFIER}"
         data-${IDENTIFIER}-base-value="${base}"
         data-${IDENTIFIER}-min-value="${min}"
         data-${IDENTIFIER}-max-value="${max}">
      ${decreaseBtn}${valueTarget}${increaseBtn}${servingsLabel}${quantityTargets}
    </div>
  `;

  app = Application.start();
  app.register(IDENTIFIER, RecipePortionsController);
  await flush();
}

function flush() {
  return new Promise<void>(resolve => setTimeout(resolve, 0));
}

function byTestId<T extends Element = HTMLElement>(id: string): T {
  const el = document.querySelector<T>(`[data-test-id="${id}"]`);
  if (!el) {
    throw new Error(`No element with data-test-id="${id}"`);
  }
  return el;
}

function clickN(testId: string, times: number) {
  const btn = byTestId<HTMLButtonElement>(testId);
  for (let i = 0; i < times; i++) {
    if (btn.disabled) break;
    btn.click();
  }
}

describe('recipe_portions_controller', () => {
  afterEach(() => {
    app?.stop();
    document.body.innerHTML = '';
  });

  describe('connection lifecycle', () => {
    it('renders the base portions count on connect', async () => {
      await mount({ base: 4 });
      expect(byTestId('portions-value').textContent).toBe('4');
    });

    it('disables decrease button when starting at min value', async () => {
      await mount({ base: 1, min: 1, max: 8 });
      expect(byTestId<HTMLButtonElement>('portions-decrease').disabled).toBe(true);
      expect(byTestId<HTMLButtonElement>('portions-increase').disabled).toBe(false);
    });

    it('disables increase button when starting at max value', async () => {
      await mount({ base: 8, min: 1, max: 8 });
      expect(byTestId<HTMLButtonElement>('portions-decrease').disabled).toBe(false);
      expect(byTestId<HTMLButtonElement>('portions-increase').disabled).toBe(true);
    });

    it('enables both buttons when starting between bounds', async () => {
      await mount({ base: 4, min: 1, max: 8 });
      expect(byTestId<HTMLButtonElement>('portions-decrease').disabled).toBe(false);
      expect(byTestId<HTMLButtonElement>('portions-increase').disabled).toBe(false);
    });
  });

  describe('decrement bounds', () => {
    it('decrements current value when decrease button is clicked', async () => {
      await mount({ base: 4 });
      clickN('portions-decrease', 1);
      expect(byTestId('portions-value').textContent).toBe('3');
    });

    it('does not decrement below min value', async () => {
      await mount({ base: 2, min: 1 });
      clickN('portions-decrease', 5);
      expect(byTestId('portions-value').textContent).toBe('1');
    });

    it('disables decrease button after reaching min through clicks', async () => {
      await mount({ base: 3, min: 1 });
      clickN('portions-decrease', 2);
      expect(byTestId<HTMLButtonElement>('portions-decrease').disabled).toBe(true);
    });
  });

  describe('increment bounds', () => {
    it('increments current value when increase button is clicked', async () => {
      await mount({ base: 4 });
      clickN('portions-increase', 1);
      expect(byTestId('portions-value').textContent).toBe('5');
    });

    it('does not increment above max value', async () => {
      await mount({ base: 23, max: 24 });
      clickN('portions-increase', 5);
      expect(byTestId('portions-value').textContent).toBe('24');
    });

    it('disables increase button after reaching max through clicks', async () => {
      await mount({ base: 22, max: 24 });
      clickN('portions-increase', 2);
      expect(byTestId<HTMLButtonElement>('portions-increase').disabled).toBe(true);
    });
  });

  describe('quantity formatting', () => {
    it.each<{
      label: string;
      base: number;
      baseQuantity: string;
      adjustments: { btn: 'portions-increase' | 'portions-decrease'; times: number };
      expected: string;
    }>([
      { label: 'integer at base ratio', base: 4, baseQuantity: '200', adjustments: { btn: 'portions-increase', times: 0 }, expected: '200' },
      { label: 'integer halved', base: 4, baseQuantity: '200', adjustments: { btn: 'portions-decrease', times: 2 }, expected: '100' },
      { label: 'fractional below 10 with French comma', base: 4, baseQuantity: '2.5', adjustments: { btn: 'portions-decrease', times: 2 }, expected: '1,3' },
      { label: 'fractional below 10 keeps non-rounded integer', base: 4, baseQuantity: '4', adjustments: { btn: 'portions-decrease', times: 2 }, expected: '2' },
      { label: 'large values rounded to integer', base: 4, baseQuantity: '500', adjustments: { btn: 'portions-increase', times: 4 }, expected: '1000' },
      { label: 'small fractional rounds to one decimal', base: 4, baseQuantity: '0.5', adjustments: { btn: 'portions-decrease', times: 2 }, expected: '0,3' },
    ])('formats $label', async ({ base, baseQuantity, adjustments, expected }) => {
      await mount({
        base,
        min: 1,
        max: 24,
        quantities: [{ id: 'qty', baseQuantity }],
      });
      clickN(adjustments.btn, adjustments.times);
      expect(byTestId('qty').textContent).toBe(expected);
    });

    it('skips quantity targets with non-numeric baseQuantity', async () => {
      await mount({
        base: 4,
        quantities: [{ id: 'qty-broken', baseQuantity: 'not-a-number' }],
      });
      const initial = byTestId('qty-broken').textContent;
      clickN('portions-increase', 1);
      expect(byTestId('qty-broken').textContent).toBe(initial);
    });
  });

  describe('servings label templates', () => {
    it('uses singular template when current value is 1', async () => {
      await mount({
        base: 2,
        min: 1,
        max: 8,
        withServingsLabel: true,
        servingsSingular: '%count% personne',
        servingsPlural: '%count% personnes',
      });
      clickN('portions-decrease', 1);
      expect(byTestId('portions-servings').textContent).toBe('1 personne');
    });

    it('uses plural template when current value is greater than 1', async () => {
      await mount({
        base: 4,
        withServingsLabel: true,
        servingsSingular: '%count% personne',
        servingsPlural: '%count% personnes',
      });
      expect(byTestId('portions-servings').textContent).toBe('4 personnes');
    });

    it('does not throw when servings label datasets are missing', async () => {
      await mount({ base: 4, withServingsLabel: true });
      expect(() => clickN('portions-increase', 1)).not.toThrow();
    });
  });

  describe('optional targets', () => {
    it('renders without throwing when optional targets are absent', async () => {
      await mount({ base: 4, withDecreaseBtn: false, withIncreaseBtn: false, withValueTarget: false });
      expect(document.querySelector('[data-test-id="portions-value"]')).toBeNull();
    });
  });
});
