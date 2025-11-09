import { Page, Locator } from '@playwright/test';

/**
 * Base Page Object Model for EasyAdmin pages
 * Provides common methods for interacting with EasyAdmin forms
 */
export class EasyAdminPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    /**
     * Fill a form field by its data-test-id
     */
    async fillField(testId: string, value: string): Promise<void> {
        await this.page.fill(`[data-test-id="${testId}"]`, value);
    }

    /**
     * Select an option from a dropdown by its data-test-id
     */
    async selectOption(testId: string, value: string | { index: number; value?: string; label?: string }): Promise<void> {
        await this.page.selectOption(`[data-test-id="${testId}"]`, value);
    }

    /**
     * Check a checkbox by its data-test-id
     */
    async check(testId: string): Promise<void> {
        await this.page.check(`[data-test-id="${testId}"]`);
    }

    /**
     * Uncheck a checkbox by its data-test-id
     */
    async uncheck(testId: string): Promise<void> {
        await this.page.uncheck(`[data-test-id="${testId}"]`);
    }

    /**
     * Click a button by its data-test-id
     */
    async clickButton(testId: string): Promise<void> {
        await this.page.click(`[data-test-id="${testId}"]`);
    }

    /**
     * Get a locator for an element by data-test-id
     */
    getElement(testId: string): Locator {
        return this.page.locator(`[data-test-id="${testId}"]`);
    }

    /**
     * Wait for an element to be visible
     */
    async waitForElement(testId: string, options?: { timeout?: number }): Promise<void> {
        await this.page.waitForSelector(`[data-test-id="${testId}"]`, {
            state: 'visible',
            ...options
        });
    }

    /**
     * Submit the form
     * Note: EasyAdmin uses different button classes, so we use a generic approach
     */
    async submitForm(): Promise<void> {
        await this.page.click('button[type="submit"]');
    }

    /**
     * Navigate to a CRUD action
     */
    async navigateToCrud(controller: string, action: string = 'index'): Promise<void> {
        const params = new URLSearchParams({
            crudAction: action,
            crudControllerFqcn: controller
        });
        await this.page.goto(`/admin?${params.toString()}`);
    }
}
