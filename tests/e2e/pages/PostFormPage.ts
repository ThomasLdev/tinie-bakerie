import { Page, expect } from '@playwright/test';
import { EasyAdminPage } from './EasyAdminPage';

/**
 * Page Object Model for Post CRUD form
 * Encapsulates all interactions with the Post create/edit form
 */
export class PostFormPage extends EasyAdminPage {
    constructor(page: Page) {
        super(page);
    }

    /**
     * Navigate to the "Create Post" page
     */
    async navigateToNew(): Promise<void> {
        await this.navigateToCrud('App\\Controller\\Admin\\PostCrudController', 'new');
    }

    /**
     * Navigate to edit a specific post
     */
    async navigateToEdit(postId: number): Promise<void> {
        const params = new URLSearchParams({
            crudAction: 'edit',
            crudControllerFqcn: 'App\\Controller\\Admin\\PostCrudController',
            entityId: postId.toString()
        });
        await this.page.goto(`/admin?${params.toString()}`);
    }

    /**
     * Fill basic post fields
     */
    async fillBasicFields(data: {
        cookingTime: string;
        difficulty: 'easy' | 'medium' | 'advanced';
        categoryIndex: number;
        active?: boolean;
    }): Promise<void> {
        await this.fillField('post-cookingtime', data.cookingTime);
        await this.selectOption('post-difficulty', data.difficulty);
        await this.selectOption('post-category', { index: data.categoryIndex });

        if (data.active !== undefined) {
            if (data.active) {
                await this.check('post-active');
            } else {
                await this.uncheck('post-active');
            }
        }
    }

    /**
     * Fill translation fields for a specific locale
     */
    async fillTranslation(index: number, data: {
        title: string;
        metaDescription: string;
        excerpt: string;
        metaTitle?: string;
        notes?: string;
    }): Promise<void> {
        await this.fillField(`post-translations-${index}-title`, data.title);
        await this.fillField(`post-translations-${index}-metadescription`, data.metaDescription);
        await this.fillField(`post-translations-${index}-excerpt`, data.excerpt);

        if (data.metaTitle) {
            await this.fillField(`post-translations-${index}-metatitle`, data.metaTitle);
        }

        if (data.notes) {
            await this.fillField(`post-translations-${index}-notes`, data.notes);
        }
    }

    /**
     * Add a media item to the collection
     * Note: The actual button selector might need adjustment based on your EasyAdmin setup
     */
    async addMediaItem(): Promise<void> {
        // Look for the collection add button
        // This selector might need to be adjusted based on actual EasyAdmin HTML
        const addButton = this.page.locator('[data-test-id*="media"]').locator('button').filter({ hasText: /add/i }).first();
        await addButton.click();
    }

    /**
     * Fill media item fields
     */
    async fillMediaItem(index: number, data: {
        position: string;
        type: 'image' | 'video';
        translationsFr: { alt: string; title: string };
        translationsEn: { alt: string; title: string };
    }): Promise<void> {
        // Wait for media item to appear
        await this.waitForElement(`post-media-${index}-position`);

        await this.fillField(`post-media-${index}-position`, data.position);
        await this.selectOption(`post-media-${index}-type`, data.type);

        // French translation
        await this.fillField(`post-media-${index}-translations-0-alt`, data.translationsFr.alt);
        await this.fillField(`post-media-${index}-translations-0-title`, data.translationsFr.title);

        // English translation
        await this.fillField(`post-media-${index}-translations-1-alt`, data.translationsEn.alt);
        await this.fillField(`post-media-${index}-translations-1-title`, data.translationsEn.title);
    }

    /**
     * Add tags to the post
     */
    async selectTags(tagIds: string[]): Promise<void> {
        for (const tagId of tagIds) {
            await this.selectOption('post-tags', tagId);
        }
    }

    /**
     * Submit the form and wait for redirect
     */
    async submit(): Promise<void> {
        await this.submitForm();
        // Wait for redirect to admin index
        await this.page.waitForURL(/\/admin\?/);
    }

    /**
     * Verify success after creating/updating a post
     * - Checks we're redirected to the index page
     * - Checks success message appears
     * - Optionally verifies the post appears in the list
     */
    async verifySuccess(options?: { postTitle?: string }): Promise<void> {
        // Verify URL is the Post index page
        await expect(this.page).toHaveURL(/\/admin\?crudAction=index&crudControllerFqcn=App\\Controller\\Admin\\PostCrudController/, { timeout: 5000 });

        // Verify success flash message
        const successMessage = this.page.locator('.alert-success, .flash-success, [role="alert"]').filter({ hasText: /created|saved|updated/i });
        await expect(successMessage).toBeVisible({ timeout: 5000 });

        // Optionally verify the post appears in the list
        // Note: This assumes EasyAdmin default sorting (newest first)
        // If custom sorting is configured, this might need adjustment
        if (options?.postTitle) {
            const postInList = this.page.locator('.datagrid tbody tr').filter({ hasText: options.postTitle });
            await expect(postInList).toBeVisible({ timeout: 5000 });
        }
    }

    /**
     * Verify validation error appears
     */
    async verifyValidationError(): Promise<void> {
        const errorMessage = this.page.locator('.invalid-feedback, .form-error-message, .alert-danger');
        await expect(errorMessage.first()).toBeVisible({ timeout: 5000 });
    }

    /**
     * Complete flow: Create a post with minimal data
     */
    async createMinimalPost(data: {
        cookingTime: string;
        difficulty: 'easy' | 'medium' | 'advanced';
        categoryIndex: number;
        titleFr: string;
        titleEn: string;
    }): Promise<void> {
        await this.navigateToNew();

        await this.fillBasicFields({
            cookingTime: data.cookingTime,
            difficulty: data.difficulty,
            categoryIndex: data.categoryIndex,
            active: true
        });

        await this.fillTranslation(0, {
            title: data.titleFr,
            metaDescription: 'A'.repeat(120),
            excerpt: 'B'.repeat(50)
        });

        await this.fillTranslation(1, {
            title: data.titleEn,
            metaDescription: 'C'.repeat(120),
            excerpt: 'D'.repeat(50)
        });

        await this.submit();
        // Verify with French title (index 0 is typically the default locale)
        await this.verifySuccess({ postTitle: data.titleFr });
    }
}
