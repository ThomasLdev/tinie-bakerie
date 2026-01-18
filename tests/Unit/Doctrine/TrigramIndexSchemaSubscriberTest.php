<?php

declare(strict_types=1);

namespace App\Tests\Unit\Doctrine;

use App\Doctrine\TrigramIndexSchemaSubscriber;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrigramIndexSchemaSubscriber::class)]
final class TrigramIndexSchemaSubscriberTest extends TestCase
{
    private TrigramIndexSchemaSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->subscriber = new TrigramIndexSchemaSubscriber();
    }

    #[TestDox('Adds trigram index when post_translation table exists')]
    public function testAddsTrigramIndexWhenTableExists(): void
    {
        $table = $this->createMock(Table::class);
        $table->expects(self::once())
            ->method('hasIndex')
            ->with('post_translation_title_trgm_idx')
            ->willReturn(false);
        $table->expects(self::once())
            ->method('addIndex')
            ->with(
                ['title'],
                'post_translation_title_trgm_idx',
                [],
                ['comment' => 'GIN trigram index managed via migration'],
            );

        $schema = self::createStub(Schema::class);
        $schema->method('hasTable')->willReturn(true);
        $schema->method('getTable')->willReturn($table);

        $event = $this->createEventArgs($schema);

        $this->subscriber->postGenerateSchema($event);
    }

    #[TestDox('Does not add index when post_translation table does not exist')]
    public function testDoesNothingWhenTableDoesNotExist(): void
    {
        $schema = $this->createMock(Schema::class);
        $schema->expects(self::once())
            ->method('hasTable')
            ->with('post_translation')
            ->willReturn(false);
        $schema->expects(self::never())->method('getTable');

        $event = $this->createEventArgs($schema);

        $this->subscriber->postGenerateSchema($event);
    }

    #[TestDox('Does not add index when it already exists')]
    public function testDoesNotAddIndexWhenAlreadyExists(): void
    {
        $table = $this->createMock(Table::class);
        $table->expects(self::once())
            ->method('hasIndex')
            ->with('post_translation_title_trgm_idx')
            ->willReturn(true);
        $table->expects(self::never())->method('addIndex');

        $schema = self::createStub(Schema::class);
        $schema->method('hasTable')->willReturn(true);
        $schema->method('getTable')->willReturn($table);

        $event = $this->createEventArgs($schema);

        $this->subscriber->postGenerateSchema($event);
    }

    private function createEventArgs(Schema&Stub $schema): GenerateSchemaEventArgs
    {
        $entityManager = self::createStub(EntityManagerInterface::class);

        return new GenerateSchemaEventArgs($entityManager, $schema);
    }
}
