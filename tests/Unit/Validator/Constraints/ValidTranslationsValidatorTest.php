<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator\Constraints;

use App\Entity\Contracts\Translation;
use App\Services\Locale\Locales;
use App\Validator\Constraints\ValidTranslations;
use App\Validator\Constraints\ValidTranslationsValidator;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * Unit tests for ValidTranslationsValidator.
 * Tests custom validation logic for translation collections.
 *
 * @internal
 */
#[CoversClass(ValidTranslationsValidator::class)]
#[CoversClass(ValidTranslations::class)]
#[AllowMockObjectsWithoutExpectations]
final class ValidTranslationsValidatorTest extends TestCase
{
    private ValidTranslationsValidator $validator;

    private MockObject&ExecutionContextInterface $context;

    private ValidTranslations $constraint;

    protected function setUp(): void
    {
        $this->validator = new ValidTranslationsValidator(new Locales('en|fr'));

        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);

        $this->constraint = new ValidTranslations();
    }

    public function testValidateWithWrongConstraintType(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $wrongConstraint = $this->createMock(Constraint::class);
        $this->validator->validate([], $wrongConstraint);
    }

    public function testValidateWithNullValue(): void
    {
        $this->context->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate(null, $this->constraint);
    }

    public function testValidateWithNonCollectionValue(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate('not a collection', $this->constraint);
    }

    public function testValidateWithCorrectNumberOfTranslations(): void
    {
        $translation1 = $this->createTranslationMock('en');
        $translation2 = $this->createTranslationMock('fr');

        $collection = new ArrayCollection([$translation1, $translation2]);

        $this->context->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate($collection, $this->constraint);
    }

    public function testValidateWithIncorrectNumberOfTranslations(): void
    {
        $translation1 = $this->createTranslationMock('en');

        $collection = new ArrayCollection([$translation1]);

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('setParameter')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->countMessage)
            ->willReturn($violationBuilder);

        $this->context->method('getObject')->willReturn(new class() {});

        $this->validator->validate($collection, $this->constraint);
    }

    public function testValidateWithDuplicateLocales(): void
    {
        $translation1 = $this->createTranslationMock('en');
        $translation2 = $this->createTranslationMock('en'); // Duplicate locale

        $collection = new ArrayCollection([$translation1, $translation2]);

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('setParameter')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->localeMessage)
            ->willReturn($violationBuilder);

        $this->context->method('getObject')->willReturn(new class() {});

        $this->validator->validate($collection, $this->constraint);
    }

    public function testValidateCountMessageParameters(): void
    {
        $translation1 = $this->createTranslationMock('en');

        $collection = new ArrayCollection([$translation1]);

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $violationBuilder->expects($this->exactly(3))
            ->method('setParameter')
            ->willReturnCallback(static function (string $key, string $value) use ($violationBuilder): MockObject {
                if ($key === '{{ count }}') {
                    self::assertSame('1', $value);
                } elseif ($key === '{{ required }}') {
                    self::assertSame('2', $value);
                } elseif ($key === '{{ entity }}') {
                    self::assertNotEmpty($value);
                }

                return $violationBuilder;
            });

        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->countMessage)
            ->willReturn($violationBuilder);

        $this->context->method('getObject')->willReturn(new class() {});

        $this->validator->validate($collection, $this->constraint);
    }

    public function testValidateLocaleMessageParameters(): void
    {
        $translation1 = $this->createTranslationMock('fr');
        $translation2 = $this->createTranslationMock('fr');

        $collection = new ArrayCollection([$translation1, $translation2]);

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $violationBuilder->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturnCallback(static function (string $key, string $value) use ($violationBuilder): MockObject {
                if ($key === '{{ locale }}') {
                    self::assertSame('fr', $value);
                } elseif ($key === '{{ entity }}') {
                    self::assertNotEmpty($value);
                }

                return $violationBuilder;
            });

        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->localeMessage)
            ->willReturn($violationBuilder);

        $this->context->method('getObject')->willReturn(new class() {});

        $this->validator->validate($collection, $this->constraint);
    }

    public function testValidateWithEmptyCollection(): void
    {
        $collection = new ArrayCollection([]);

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('setParameter')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->countMessage)
            ->willReturn($violationBuilder);

        $this->context->method('getObject')->willReturn(new class() {});

        $this->validator->validate($collection, $this->constraint);
    }

    public function testValidateWithThreeLocalesConfigured(): void
    {
        $validator = new ValidTranslationsValidator(new Locales('en|fr|de'));
        $validator->initialize($this->context);

        $translation1 = $this->createTranslationMock('en');
        $translation2 = $this->createTranslationMock('fr');
        $translation3 = $this->createTranslationMock('de');

        $collection = new ArrayCollection([$translation1, $translation2, $translation3]);

        $this->context->expects(self::never())
            ->method('buildViolation');

        $validator->validate($collection, $this->constraint);
    }

    public function testValidateIgnoresNonTranslationObjectsInDuplicateCheck(): void
    {
        // The validator counts the collection size first, so it will fail if count != requiredCount
        // But in the locale duplicate check loop, non-translation objects are skipped
        $translation1 = $this->createTranslationMock('en');
        $translation2 = $this->createTranslationMock('fr');
        $nonTranslation = new \stdClass();

        $collection = new ArrayCollection([$translation1, $translation2, $nonTranslation]);

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('setParameter')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        // Will fail count check (3 items but 2 locales required)
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->countMessage)
            ->willReturn($violationBuilder);

        $this->context->method('getObject')->willReturn(new class() {});

        $this->validator->validate($collection, $this->constraint);
    }

    public function testGetEntityNameWithObject(): void
    {
        $translation1 = $this->createTranslationMock('en');
        $collection = new ArrayCollection([$translation1]);

        $testEntity = new class() {
        };

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('setParameter')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->willReturn($violationBuilder);

        $this->context->method('getObject')->willReturn($testEntity);

        $this->validator->validate($collection, $this->constraint);
    }

    public function testGetEntityNameWithNonObject(): void
    {
        $translation1 = $this->createTranslationMock('en');
        $collection = new ArrayCollection([$translation1]);

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $violationBuilder->expects($this->exactly(3))
            ->method('setParameter')
            ->willReturnCallback(static function (string $key, string $value) use ($violationBuilder): MockObject {
                if ($key === '{{ entity }}') {
                    self::assertSame('Entity', $value);
                }

                return $violationBuilder;
            });

        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->willReturn($violationBuilder);

        $this->context->method('getObject')->willReturn(null);

        $this->validator->validate($collection, $this->constraint);
    }

    public function testRequiredCountIsSetCorrectly(): void
    {
        $validator = new ValidTranslationsValidator(new Locales('en|fr|de'));

        self::assertSame(3, $validator->requiredCount);
    }

    public function testConstraintTarget(): void
    {
        self::assertSame(Constraint::PROPERTY_CONSTRAINT, $this->constraint->getTargets());
    }

    /**
     * Creates a mock Translation object with the given locale.
     */
    private function createTranslationMock(string $locale): Translation&MockObject
    {
        $translation = $this->createMock(Translation::class);
        $translation->method('getLocale')->willReturn($locale);

        return $translation;
    }
}
