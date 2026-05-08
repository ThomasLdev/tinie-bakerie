import { Application } from '@hotwired/stimulus';
import RecipeChecklistController from '@assets/controllers/recipe_checklist_controller.js';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const IDENTIFIER = 'recipe-checklist';

interface MountOptions {
  storageKey?: string;
  total?: number;
  itemCount?: number;
  withCount?: boolean;
  withProgress?: boolean;
  preCheck?: number[];
}

let app: Application;

async function mount(options: MountOptions = {}) {
  const {
    storageKey = '',
    total,
    itemCount = 4,
    withCount = true,
    withProgress = true,
    preCheck = [],
  } = options;

  const items = Array.from({ length: itemCount }, (_, i) => {
    const checked = preCheck.includes(i) ? 'checked' : '';
    return `<input type="checkbox" data-test-id="item-${i}" data-${IDENTIFIER}-target="item" data-action="change->${IDENTIFIER}#updateCount" ${checked} />`;
  }).join('');

  const countTarget = withCount
    ? `<span data-test-id="checklist-count" data-${IDENTIFIER}-target="count">0</span>`
    : '';
  const progressTarget = withProgress
    ? `<div data-test-id="checklist-progress" data-${IDENTIFIER}-target="progress" style="--progress: 0"></div>`
    : '';

  const totalAttr = total !== undefined ? `data-${IDENTIFIER}-total-value="${total}"` : '';
  const storageAttr = storageKey ? `data-${IDENTIFIER}-storage-key-value="${storageKey}"` : '';

  document.body.innerHTML = `
    <div data-controller="${IDENTIFIER}" ${totalAttr} ${storageAttr}>
      ${items}${countTarget}${progressTarget}
    </div>
  `;

  app = Application.start();
  app.register(IDENTIFIER, RecipeChecklistController);
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

function checkItem(index: number) {
  const item = byTestId<HTMLInputElement>(`item-${index}`);
  item.checked = true;
  item.dispatchEvent(new Event('change', { bubbles: true }));
}

describe('recipe_checklist_controller', () => {
  beforeEach(() => {
    localStorage.clear();
  });

  afterEach(() => {
    app?.stop();
    document.body.innerHTML = '';
    localStorage.clear();
  });

  describe('count rendering', () => {
    it('initializes count to 0 when no items are checked', async () => {
      await mount({ itemCount: 4 });
      expect(byTestId('checklist-count').textContent).toBe('0');
    });

    it('updates count when an item is checked', async () => {
      await mount({ itemCount: 4 });
      checkItem(0);
      expect(byTestId('checklist-count').textContent).toBe('1');
    });

    it('updates count when an item is unchecked', async () => {
      await mount({ itemCount: 4, preCheck: [0, 1] });
      const item = byTestId<HTMLInputElement>('item-0');
      item.checked = false;
      item.dispatchEvent(new Event('change', { bubbles: true }));
      expect(byTestId('checklist-count').textContent).toBe('1');
    });
  });

  describe('progress ratio', () => {
    it('computes progress as percentage of totalValue when defined', async () => {
      await mount({ itemCount: 4, total: 4, preCheck: [0] });
      const progress = byTestId('checklist-progress');
      expect(progress.style.getPropertyValue('--progress')).toBe('25');
      expect(progress.dataset.count).toBe('1');
      expect(progress.dataset.total).toBe('4');
    });

    it('falls back to itemTargets length when totalValue is 0', async () => {
      await mount({ itemCount: 4, preCheck: [0, 1] });
      expect(byTestId('checklist-progress').style.getPropertyValue('--progress')).toBe('50');
    });

    it('renders zero progress when there are no items at all', async () => {
      await mount({ itemCount: 0 });
      expect(byTestId('checklist-progress').style.getPropertyValue('--progress')).toBe('0');
    });

    it('rounds the ratio to nearest integer', async () => {
      await mount({ itemCount: 3, preCheck: [0] });
      // 1/3 = 33.33… → rounds to 33
      expect(byTestId('checklist-progress').style.getPropertyValue('--progress')).toBe('33');
    });
  });

  describe('localStorage persistence', () => {
    it('persists checked state to localStorage when storageKey is set', async () => {
      await mount({ itemCount: 3, storageKey: 'recipe-1' });
      checkItem(0);
      checkItem(2);
      expect(localStorage.getItem('recipe-1')).toBe(JSON.stringify([true, false, true]));
    });

    it('does not persist when storageKey is empty', async () => {
      await mount({ itemCount: 3 });
      checkItem(0);
      expect(localStorage.length).toBe(0);
    });

    it('restores checked state from localStorage on connect', async () => {
      localStorage.setItem('recipe-2', JSON.stringify([true, false, true]));
      await mount({ itemCount: 3, storageKey: 'recipe-2' });
      expect(byTestId<HTMLInputElement>('item-0').checked).toBe(true);
      expect(byTestId<HTMLInputElement>('item-1').checked).toBe(false);
      expect(byTestId<HTMLInputElement>('item-2').checked).toBe(true);
    });

    it('reflects restored state in the count target on connect', async () => {
      localStorage.setItem('recipe-3', JSON.stringify([true, true, false]));
      await mount({ itemCount: 3, storageKey: 'recipe-3' });
      expect(byTestId('checklist-count').textContent).toBe('2');
    });

    it('ignores corrupt JSON in localStorage entry', async () => {
      localStorage.setItem('recipe-4', '{not valid json');
      await mount({ itemCount: 3, storageKey: 'recipe-4' });
      expect(byTestId<HTMLInputElement>('item-0').checked).toBe(false);
      expect(byTestId('checklist-count').textContent).toBe('0');
    });

    it('ignores non-array payload in localStorage entry', async () => {
      localStorage.setItem('recipe-5', JSON.stringify({ checked: [true, false] }));
      await mount({ itemCount: 3, storageKey: 'recipe-5' });
      expect(byTestId<HTMLInputElement>('item-0').checked).toBe(false);
    });

    it('does nothing when storage entry is missing', async () => {
      await mount({ itemCount: 3, storageKey: 'recipe-missing' });
      expect(byTestId<HTMLInputElement>('item-0').checked).toBe(false);
    });

    it('does not throw when localStorage setItem throws', async () => {
      const original = Storage.prototype.setItem;
      const spy = vi.spyOn(Storage.prototype, 'setItem').mockImplementation(() => {
        throw new Error('QuotaExceededError');
      });
      try {
        await mount({ itemCount: 2, storageKey: 'recipe-quota' });
        expect(() => checkItem(0)).not.toThrow();
      } finally {
        spy.mockRestore();
        Storage.prototype.setItem = original;
      }
    });
  });

  describe('reset action', () => {
    it('unchecks all items and updates count', async () => {
      await mount({ itemCount: 4, preCheck: [0, 1, 2] });
      const controller = app.getControllerForElementAndIdentifier(
        document.querySelector(`[data-controller="${IDENTIFIER}"]`)!,
        IDENTIFIER,
      );
      expect(controller).not.toBeNull();
      (controller as unknown as { reset: () => void }).reset();
      expect(byTestId<HTMLInputElement>('item-0').checked).toBe(false);
      expect(byTestId<HTMLInputElement>('item-1').checked).toBe(false);
      expect(byTestId<HTMLInputElement>('item-2').checked).toBe(false);
      expect(byTestId('checklist-count').textContent).toBe('0');
    });
  });

  describe('optional targets', () => {
    it('does not throw when count target is absent', async () => {
      await mount({ itemCount: 2, withCount: false });
      expect(() => checkItem(0)).not.toThrow();
    });

    it('does not throw when progress target is absent', async () => {
      await mount({ itemCount: 2, withProgress: false });
      expect(() => checkItem(0)).not.toThrow();
    });
  });
});
