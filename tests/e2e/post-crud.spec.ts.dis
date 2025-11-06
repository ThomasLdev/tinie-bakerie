import { test, expect } from '@playwright/test';
import { PostFormPage } from './pages/PostFormPage';

/**
 * E2E Tests: Post CRUD with Media
 * 
 * Tests the "dead angles" that can't be tested with PHPUnit:
 * 1. JavaScript collection item creation (clicking "Add Media" button)
 * 2. Full integration from UI to database with cascades
 * 
 * Note: Form validation edge cases are tested in tests/Form/Type/PostTypeTest.php
 * Note: Database persistence/cascades are tested in tests/Integration/Entity/PostPersistenceTest.php
 */
test.describe('Post CRUD', () => {
  
  test.beforeEach(async ({ page }) => {
    // TODO: Load fixtures or ensure test data exists
    // For now, assumes categories exist from fixtures
  });

  test('should create post with minimal data', async ({ page }) => {
    const postForm = new PostFormPage(page);
    
    await postForm.createMinimalPost({
      cookingTime: '30',
      difficulty: 'easy',
      categoryIndex: 1,
      titleFr: 'Test Post E2E FR',
      titleEn: 'Test Post E2E EN'
    });
  });

  test('should create post with complete data', async ({ page }) => {
    const postForm = new PostFormPage(page);
    
    await postForm.navigateToNew();
    
    // Fill basic fields
    await postForm.fillBasicFields({
      cookingTime: '45',
      difficulty: 'medium',
      categoryIndex: 1,
      active: true
    });
    
    // Fill French translation
    await postForm.fillTranslation(0, {
      title: 'Complete Post FR',
      metaTitle: 'Meta Title FR',
      metaDescription: 'A'.repeat(120),
      excerpt: 'B'.repeat(50),
      notes: 'Some notes in French'
    });
    
    // Fill English translation
    await postForm.fillTranslation(1, {
      title: 'Complete Post EN',
      metaTitle: 'Meta Title EN',
      metaDescription: 'C'.repeat(120),
      excerpt: 'D'.repeat(50),
      notes: 'Some notes in English'
    });
    
    await postForm.submit();
    await postForm.verifySuccess({ postTitle: 'Complete Post FR' });
  });

  test('should create inactive post', async ({ page }) => {
    const postForm = new PostFormPage(page);
    
    await postForm.navigateToNew();
    
    await postForm.fillBasicFields({
      cookingTime: '60',
      difficulty: 'advanced',
      categoryIndex: 1,
      active: false  // Inactive post
    });
    
    await postForm.fillTranslation(0, {
      title: 'Inactive Post FR',
      metaDescription: 'A'.repeat(120),
      excerpt: 'B'.repeat(50)
    });
    
    await postForm.fillTranslation(1, {
      title: 'Inactive Post EN',
      metaDescription: 'C'.repeat(120),
      excerpt: 'D'.repeat(50)
    });
    
    await postForm.submit();
    await postForm.verifySuccess({ postTitle: 'Inactive Post FR' });
  });

  test('should create post with media via UI', async ({ page }) => {
    const postForm = new PostFormPage(page);
    
    await postForm.navigateToNew();
    
    // Fill basic post data
    await postForm.fillBasicFields({
      cookingTime: '35',
      difficulty: 'easy',
      categoryIndex: 1,
      active: true
    });
    
    await postForm.fillTranslation(0, {
      title: 'Post With Media FR',
      metaDescription: 'A'.repeat(120),
      excerpt: 'B'.repeat(50)
    });
    
    await postForm.fillTranslation(1, {
      title: 'Post With Media EN',
      metaDescription: 'C'.repeat(120),
      excerpt: 'D'.repeat(50)
    });
    
    // ✅ DEAD ANGLE: JavaScript adds collection item
    await postForm.addMediaItem();
    
    // Fill media item
    await postForm.fillMediaItem(0, {
      position: '0',
      type: 'image',
      translationsFr: {
        alt: 'Image alt FR minimum',
        title: 'Title FR'
      },
      translationsEn: {
        alt: 'Image alt EN minimum',
        title: 'Title EN'
      }
    });
    
    await postForm.submit();
    await postForm.verifySuccess({ postTitle: 'Post With Media FR' });
  });

  test('should create post with multiple media items', async ({ page }) => {
    const postForm = new PostFormPage(page);
    
    await postForm.navigateToNew();
    
    await postForm.fillBasicFields({
      cookingTime: '40',
      difficulty: 'medium',
      categoryIndex: 1,
      active: true
    });
    
    await postForm.fillTranslation(0, {
      title: 'Post With Multiple Media FR',
      metaDescription: 'A'.repeat(120),
      excerpt: 'B'.repeat(50)
    });
    
    await postForm.fillTranslation(1, {
      title: 'Post With Multiple Media EN',
      metaDescription: 'C'.repeat(120),
      excerpt: 'D'.repeat(50)
    });
    
    // Add first media item
    await postForm.addMediaItem();
    await postForm.fillMediaItem(0, {
      position: '0',
      type: 'image',
      translationsFr: {
        alt: 'First media alt FR',
        title: 'First Media FR'
      },
      translationsEn: {
        alt: 'First media alt EN',
        title: 'First Media EN'
      }
    });
    
    // Add second media item
    await postForm.addMediaItem();
    await postForm.fillMediaItem(1, {
      position: '1',
      type: 'video',
      translationsFr: {
        alt: 'Second media alt FR',
        title: 'Second Media FR'
      },
      translationsEn: {
        alt: 'Second media alt EN',
        title: 'Second Media EN'
      }
    });
    
    await postForm.submit();
    await postForm.verifySuccess({ postTitle: 'Post With Multiple Media FR' });
  });

  test('should show validation error for missing required fields', async ({ page }) => {
    const postForm = new PostFormPage(page);
    
    await postForm.navigateToNew();
    
    // Fill only cooking time, missing other required fields
    await postForm.fillField('post-cookingtime', '30');
    
    await postForm.submitForm();
    
    // Should show validation errors (not redirect)
    await postForm.verifyValidationError();
  });

  test('should show validation error for invalid cooking time', async ({ page }) => {
    const postForm = new PostFormPage(page);
    
    await postForm.navigateToNew();
    
    await postForm.fillBasicFields({
      cookingTime: '2000',  // Invalid: too high
      difficulty: 'easy',
      categoryIndex: 1
    });
    
    await postForm.fillTranslation(0, {
      title: 'Test Invalid Time FR',
      metaDescription: 'A'.repeat(120),
      excerpt: 'B'.repeat(50)
    });
    
    await postForm.fillTranslation(1, {
      title: 'Test Invalid Time EN',
      metaDescription: 'C'.repeat(120),
      excerpt: 'D'.repeat(50)
    });
    
    await postForm.submitForm();
    await postForm.verifyValidationError();
  });
});
