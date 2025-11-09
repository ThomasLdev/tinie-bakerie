<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services\Slug;

use App\Services\Slug\Slugger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Slugger::class)]
final class SluggerTest extends TestCase
{
    private Slugger $slugger;

    protected function setUp(): void
    {
        $this->slugger = new Slugger();
    }

    public static function provideBasicSlugificationScenarios(): iterable
    {
        yield 'simple lowercase text returns as-is' => [
            'input' => 'hello',
            'expected' => 'hello',
        ];

        yield 'simple text with spaces converts to hyphens' => [
            'input' => 'hello world',
            'expected' => 'hello-world',
        ];

        yield 'uppercase text is lowercased' => [
            'input' => 'HELLO WORLD',
            'expected' => 'hello-world',
        ];

        yield 'mixed case text is lowercased' => [
            'input' => 'Hello World',
            'expected' => 'hello-world',
        ];

        yield 'text with multiple spaces converts to single hyphen' => [
            'input' => 'hello    world',
            'expected' => 'hello-world',
        ];

        yield 'text with leading spaces is trimmed' => [
            'input' => '   hello world',
            'expected' => 'hello-world',
        ];

        yield 'text with trailing spaces is trimmed' => [
            'input' => 'hello world   ',
            'expected' => 'hello-world',
        ];

        yield 'text with leading and trailing spaces is trimmed' => [
            'input' => '   hello world   ',
            'expected' => 'hello-world',
        ];
    }

    public static function provideSpecialCharacterScenarios(): iterable
    {
        yield 'text with exclamation marks converts to hyphens' => [
            'input' => 'hello!world!',
            'expected' => 'hello-world',
        ];

        yield 'text with question marks converts to hyphens' => [
            'input' => 'hello?world?',
            'expected' => 'hello-world',
        ];

        yield 'text with commas converts to hyphens' => [
            'input' => 'hello,world,test',
            'expected' => 'hello-world-test',
        ];

        yield 'text with periods converts to hyphens' => [
            'input' => 'hello.world.test',
            'expected' => 'hello-world-test',
        ];

        yield 'text with apostrophes converts to hyphens' => [
            'input' => "it's a test",
            'expected' => 'it-s-a-test',
        ];

        yield 'text with quotes removes them' => [
            'input' => '"hello world"',
            'expected' => 'hello-world',
        ];

        yield 'text with parentheses removes them' => [
            'input' => 'hello (world)',
            'expected' => 'hello-world',
        ];

        yield 'text with square brackets removes them' => [
            'input' => 'hello [world]',
            'expected' => 'hello-world',
        ];

        yield 'text with curly braces removes them' => [
            'input' => 'hello {world}',
            'expected' => 'hello-world',
        ];

        yield 'text with forward slashes converts to hyphens' => [
            'input' => 'hello/world',
            'expected' => 'hello-world',
        ];

        yield 'text with backslashes converts to hyphens' => [
            'input' => 'hello\\world',
            'expected' => 'hello-world',
        ];

        yield 'text with ampersands removes them' => [
            'input' => 'hello & world',
            'expected' => 'hello-world',
        ];

        yield 'text with at symbols converts to hyphens' => [
            'input' => 'hello@world',
            'expected' => 'hello-world',
        ];

        yield 'text with hash symbols converts to hyphens' => [
            'input' => 'hello#world',
            'expected' => 'hello-world',
        ];

        yield 'text with dollar signs converts to hyphens' => [
            'input' => 'hello$world',
            'expected' => 'hello-world',
        ];

        yield 'text with percent signs converts to hyphens' => [
            'input' => 'hello%world',
            'expected' => 'hello-world',
        ];

        yield 'text with asterisks converts to hyphens' => [
            'input' => 'hello*world',
            'expected' => 'hello-world',
        ];

        yield 'text with plus signs converts to hyphens' => [
            'input' => 'hello+world',
            'expected' => 'hello-world',
        ];

        yield 'text with equals signs converts to hyphens' => [
            'input' => 'hello=world',
            'expected' => 'hello-world',
        ];

        yield 'text with colons converts to hyphens' => [
            'input' => 'hello:world',
            'expected' => 'hello-world',
        ];

        yield 'text with semicolons converts to hyphens' => [
            'input' => 'hello;world',
            'expected' => 'hello-world',
        ];

        yield 'text with pipe symbols converts to hyphens' => [
            'input' => 'hello|world',
            'expected' => 'hello-world',
        ];

        yield 'text with less than symbols converts to hyphens' => [
            'input' => 'hello<world',
            'expected' => 'hello-world',
        ];

        yield 'text with greater than symbols converts to hyphens' => [
            'input' => 'hello>world',
            'expected' => 'hello-world',
        ];

        yield 'text with multiple special characters converts to hyphens' => [
            'input' => 'hello!@#$%^&*()world',
            'expected' => 'hello-world',
        ];

        yield 'text with mixed special characters and spaces' => [
            'input' => 'hello! world? test.',
            'expected' => 'hello-world-test',
        ];
    }

    public static function provideAccentedCharacterScenarios(): iterable
    {
        yield 'text with French accents é è ê ë' => [
            'input' => 'café crème',
            'expected' => 'cafe-creme',
        ];

        yield 'text with French accents à â' => [
            'input' => 'pâté à la française',
            'expected' => 'pate-a-la-francaise',
        ];

        yield 'text with French accents ç' => [
            'input' => 'ça va',
            'expected' => 'ca-va',
        ];

        yield 'text with French accents ï î' => [
            'input' => 'maïs naïf',
            'expected' => 'mais-naif',
        ];

        yield 'text with French accents ô' => [
            'input' => 'hôtel',
            'expected' => 'hotel',
        ];

        yield 'text with French accents ù û ü' => [
            'input' => 'où brûlé',
            'expected' => 'ou-brule',
        ];

        yield 'text with German umlauts ä ö ü' => [
            'input' => 'Äpfel Öl Über',
            'expected' => 'apfel-ol-uber',
        ];

        yield 'text with German ß' => [
            'input' => 'Straße',
            'expected' => 'strasse',
        ];

        yield 'text with Spanish ñ' => [
            'input' => 'España',
            'expected' => 'espana',
        ];

        yield 'text with Spanish accented vowels' => [
            'input' => 'José María',
            'expected' => 'jose-maria',
        ];

        yield 'text with Portuguese ã õ' => [
            'input' => 'São João',
            'expected' => 'sao-joao',
        ];

        yield 'text with Italian accents' => [
            'input' => 'perché così',
            'expected' => 'perche-cosi',
        ];

        yield 'text with Scandinavian characters å' => [
            'input' => 'Åsmund',
            'expected' => 'asmund',
        ];

        yield 'text with Scandinavian characters æ ø' => [
            'input' => 'København',
            'expected' => 'kobenhavn',
        ];

        yield 'text with Polish characters ł' => [
            'input' => 'Łódź',
            'expected' => 'lodz',
        ];

        yield 'text with Polish characters ą ę ć ń ś ź ż' => [
            'input' => 'zażółć gęślą jaźń',
            'expected' => 'zazolc-gesla-jazn',
        ];

        yield 'text with Czech characters č š ž' => [
            'input' => 'Češka',
            'expected' => 'ceska',
        ];

        yield 'text with Romanian characters ă â î ș ț' => [
            'input' => 'România',
            'expected' => 'romania',
        ];

        yield 'text with Turkish characters ı İ ğ ş' => [
            'input' => 'Türkiye İstanbul',
            'expected' => 'turkiye-istanbul',
        ];

        yield 'text with multiple mixed accents' => [
            'input' => 'Café élégant überall',
            'expected' => 'cafe-elegant-uberall',
        ];
    }

    public static function provideNumberScenarios(): iterable
    {
        yield 'text with single digit numbers' => [
            'input' => 'hello 1 world',
            'expected' => 'hello-1-world',
        ];

        yield 'text with multiple digit numbers' => [
            'input' => 'hello 123 world',
            'expected' => 'hello-123-world',
        ];

        yield 'text starting with numbers' => [
            'input' => '123 hello world',
            'expected' => '123-hello-world',
        ];

        yield 'text ending with numbers' => [
            'input' => 'hello world 123',
            'expected' => 'hello-world-123',
        ];

        yield 'text with only numbers' => [
            'input' => '123456',
            'expected' => '123456',
        ];

        yield 'text with numbers and special characters' => [
            'input' => 'test-123.456',
            'expected' => 'test-123-456',
        ];

        yield 'text with mixed numbers and letters' => [
            'input' => 'abc123def456',
            'expected' => 'abc123def456',
        ];
    }

    public static function provideEdgeCaseScenarios(): iterable
    {
        yield 'empty string returns empty string' => [
            'input' => '',
            'expected' => '',
        ];

        yield 'single space returns empty string' => [
            'input' => ' ',
            'expected' => '',
        ];

        yield 'multiple spaces return empty string' => [
            'input' => '    ',
            'expected' => '',
        ];

        yield 'only special characters returns empty string' => [
            'input' => '!@#$%^&*()',
            'expected' => '',
        ];

        yield 'single character' => [
            'input' => 'a',
            'expected' => 'a',
        ];

        yield 'single uppercase character' => [
            'input' => 'A',
            'expected' => 'a',
        ];

        yield 'single hyphen returns empty string' => [
            'input' => '-',
            'expected' => '',
        ];

        yield 'multiple hyphens return empty string' => [
            'input' => '---',
            'expected' => '',
        ];

        yield 'text with only hyphens and spaces returns empty string' => [
            'input' => '- - -',
            'expected' => '',
        ];

        yield 'very long string is handled' => [
            'input' => str_repeat('hello world ', 100),
            'expected' => str_repeat('hello-world-', 99) . 'hello-world',
        ];

        yield 'text with consecutive special characters and spaces' => [
            'input' => 'hello!!!   world???   test',
            'expected' => 'hello-world-test',
        ];

        yield 'text with mixed consecutive delimiters' => [
            'input' => 'hello!@# $%^ world',
            'expected' => 'hello-world',
        ];
    }

    public static function provideUnicodeScenarios(): iterable
    {
        yield 'text with emoji heart' => [
            'input' => 'hello ❤ world',
            'expected' => 'hello-world',
        ];

        yield 'text with emoji smiley' => [
            'input' => 'hello 😊 world',
            'expected' => 'hello-world',
        ];

        yield 'text with multiple emojis' => [
            'input' => 'hello 😊🎉🎊 world',
            'expected' => 'hello-world',
        ];

        yield 'text with emoji at start' => [
            'input' => '🎉 hello world',
            'expected' => 'hello-world',
        ];

        yield 'text with emoji at end' => [
            'input' => 'hello world 🎉',
            'expected' => 'hello-world',
        ];

        yield 'text with Greek characters' => [
            'input' => 'Αθήνα',
            'expected' => 'athena',
        ];

        yield 'text with Cyrillic characters' => [
            'input' => 'Москва',
            'expected' => 'moskva',
        ];

        yield 'text with Arabic characters' => [
            'input' => 'مرحبا',
            'expected' => 'mrhba',
        ];

        yield 'text with Chinese characters' => [
            'input' => '你好世界',
            'expected' => 'ni-hao-shi-jie',
        ];

        yield 'text with Japanese Hiragana' => [
            'input' => 'こんにちは',
            'expected' => 'kon-nichiha',
        ];

        yield 'text with Japanese Katakana' => [
            'input' => 'カタカナ',
            'expected' => 'katakana',
        ];

        yield 'text with Korean Hangul' => [
            'input' => '안녕하세요',
            'expected' => 'annyeonghaseyo',
        ];

        yield 'text with Hebrew characters' => [
            'input' => 'שלום',
            'expected' => 'slwm',
        ];

        yield 'text with Thai characters' => [
            'input' => 'สวัสดี',
            'expected' => 'swasdi',
        ];

        yield 'text with currency symbols' => [
            'input' => 'price £100 €200 ¥300',
            'expected' => 'price-100-eur200-300',
        ];

        yield 'text with mathematical symbols' => [
            'input' => 'x ± y × z ÷ w',
            'expected' => 'x-y-z-w',
        ];

        yield 'text with copyright and trademark symbols' => [
            'input' => 'Product© Name™',
            'expected' => 'product-c-nametm',
        ];
    }

    public static function provideRealWorldScenarios(): iterable
    {
        yield 'blog post title in English' => [
            'input' => 'How to Learn PHP in 2024',
            'expected' => 'how-to-learn-php-in-2024',
        ];

        yield 'blog post title in French' => [
            'input' => 'Les meilleures recettes françaises',
            'expected' => 'les-meilleures-recettes-francaises',
        ];

        yield 'product name with brand' => [
            'input' => 'iPhone 15 Pro Max (256GB)',
            'expected' => 'iphone-15-pro-max-256gb',
        ];

        yield 'article title with question' => [
            'input' => 'What is the meaning of life?',
            'expected' => 'what-is-the-meaning-of-life',
        ];

        yield 'title with quotation' => [
            'input' => 'The "Best" Guide Ever!',
            'expected' => 'the-best-guide-ever',
        ];

        yield 'category name with ampersand' => [
            'input' => 'Food & Drinks',
            'expected' => 'food-drinks',
        ];

        yield 'file name with version' => [
            'input' => 'document-v1.2.3-final',
            'expected' => 'document-v1-2-3-final',
        ];

        yield 'SEO-friendly URL structure' => [
            'input' => 'Top 10 JavaScript Frameworks in 2024',
            'expected' => 'top-10-javascript-frameworks-in-2024',
        ];

        yield 'multi-language text' => [
            'input' => 'Café "Über" España',
            'expected' => 'cafe-uber-espana',
        ];

        yield 'technical term with symbols' => [
            'input' => 'C++ Programming Guide',
            'expected' => 'c-programming-guide',
        ];
    }

    #[DataProvider('provideBasicSlugificationScenarios')]
    #[DataProvider('provideSpecialCharacterScenarios')]
    #[DataProvider('provideAccentedCharacterScenarios')]
    #[DataProvider('provideNumberScenarios')]
    #[DataProvider('provideEdgeCaseScenarios')]
    #[DataProvider('provideUnicodeScenarios')]
    #[DataProvider('provideRealWorldScenarios')]
    public function testSlugification(string $input, string $expected): void
    {
        self::assertSame($expected, $this->slugger->slugify($input));
    }
}
