<?php

declare(strict_types=1);

namespace App\Tests\Unit\Twig;

use App\Twig\TestExtension;
use PHPUnit\Framework\TestCase;
use Twig\Markup;

/**
 * Unit tests for TestExtension.
 * Tests Twig extension that adds data-test-id attributes for E2E testing.
 *
 * @internal
 */
final class TestExtensionTest extends TestCase
{
    public function testRenderTestIdInTestEnvironment(): void
    {
        $extension = new TestExtension('test');

        $result = $extension->renderTestId('submit-button');

        self::assertInstanceOf(Markup::class, $result);
        self::assertSame('data-test-id="submit-button"', (string) $result);
    }

    public function testRenderTestIdInProdEnvironmentReturnsEmpty(): void
    {
        $extension = new TestExtension('prod');

        $result = $extension->renderTestId('submit-button');

        self::assertSame('', $result);
    }

    public function testRenderTestIdInDevEnvironmentReturnsEmpty(): void
    {
        $extension = new TestExtension('dev');

        $result = $extension->renderTestId('submit-button');

        self::assertSame('', $result);
    }

    public function testRenderTestIdEscapesHtmlSpecialChars(): void
    {
        $extension = new TestExtension('test');

        $result = $extension->renderTestId('button-with-"quotes"');

        self::assertInstanceOf(Markup::class, $result);
        self::assertSame('data-test-id="button-with-&quot;quotes&quot;"', (string) $result);
    }

    public function testRenderTestIdHandlesXssAttempt(): void
    {
        $extension = new TestExtension('test');

        $result = $extension->renderTestId('malicious"><script>alert("xss")</script><div id="');

        self::assertInstanceOf(Markup::class, $result);
        self::assertStringNotContainsString('<script>', (string) $result);
        self::assertStringNotContainsString('</script>', (string) $result);
        self::assertStringContainsString('&lt;script&gt;', (string) $result);
    }

    public function testRenderTestIdWithDynamicValue(): void
    {
        $extension = new TestExtension('test');

        $postId = 123;
        $result = $extension->renderTestId('post-' . $postId);

        self::assertInstanceOf(Markup::class, $result);
        self::assertSame('data-test-id="post-123"', (string) $result);
    }

    public function testRenderTestIdWithSpecialCharacters(): void
    {
        $extension = new TestExtension('test');

        $result = $extension->renderTestId('test-id-with-dashes_and_underscores');

        self::assertInstanceOf(Markup::class, $result);
        self::assertSame('data-test-id="test-id-with-dashes_and_underscores"', (string) $result);
    }

    public function testRenderTestIdWithAmpersand(): void
    {
        $extension = new TestExtension('test');

        $result = $extension->renderTestId('form&field');

        self::assertInstanceOf(Markup::class, $result);
        self::assertSame('data-test-id="form&amp;field"', (string) $result);
    }

    public function testRenderTestIdWithLessThanAndGreaterThan(): void
    {
        $extension = new TestExtension('test');

        $result = $extension->renderTestId('test<>value');

        self::assertInstanceOf(Markup::class, $result);
        self::assertSame('data-test-id="test&lt;&gt;value"', (string) $result);
    }

    public function testRenderTestIdWithEmptyString(): void
    {
        $extension = new TestExtension('test');

        $result = $extension->renderTestId('');

        self::assertInstanceOf(Markup::class, $result);
        self::assertSame('data-test-id=""', (string) $result);
    }

    public function testRenderTestIdMarkupIsNotDoubleEscaped(): void
    {
        $extension = new TestExtension('test');

        $result = $extension->renderTestId('test-id');

        self::assertInstanceOf(Markup::class, $result);
        
        // Markup should prevent double-escaping when rendered in Twig
        $markup = new Markup((string) $result, 'UTF-8');
        self::assertSame('data-test-id="test-id"', (string) $markup);
    }

    public function testRenderTestIdOnlyRendersInTestEnvironment(): void
    {
        $environments = [
            'test' => true,  // Should render
            'prod' => false, // Should not render
            'dev' => false,  // Should not render
        ];

        foreach ($environments as $env => $shouldRender) {
            $extension = new TestExtension($env);
            $result = $extension->renderTestId('test-button');

            if ($shouldRender) {
                self::assertInstanceOf(Markup::class, $result, "Should render Markup in {$env} environment");
                self::assertStringContainsString('data-test-id', (string) $result);
            } else {
                self::assertSame('', $result, "Should return empty string in {$env} environment");
            }
        }
    }

    public function testRenderTestIdWithUnicodeCharacters(): void
    {
        $extension = new TestExtension('test');

        $result = $extension->renderTestId('button-émoji-🚀');

        self::assertInstanceOf(Markup::class, $result);
        self::assertStringContainsString('data-test-id=', (string) $result);
        self::assertStringContainsString('émoji', (string) $result);
    }

    public function testRenderTestIdUsesCorrectEncoding(): void
    {
        $extension = new TestExtension('test');

        $result = $extension->renderTestId('test-id');

        self::assertInstanceOf(Markup::class, $result);
        
        $reflection = new \ReflectionClass($result);
        $charsetProperty = $reflection->getProperty('charset');
        $charsetProperty->setAccessible(true);
        
        self::assertSame('UTF-8', $charsetProperty->getValue($result));
    }
}
