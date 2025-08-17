<?php

namespace App\Tests\Unit\Command;

use App\Command\CreateUploadDirsCommand;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(CreateUploadDirsCommand::class)]
class CreateUploadDirsCommandTest extends KernelTestCase
{
    private Filesystem $fileSystem;

    private CreateUploadDirsCommand $command;

    public static function getUploadDirsDataProvider(): array
    {
        return [
            'create directories when none exist' => [
                'expected' => 'OK',
                'existingDirectories' => [],
                'directoriesToCreate' => [
                    '/app/public/upload/category',
                    '/app/public/upload/post',
                    '/app/public/upload/post_section',
                ],
                'arguments' => [],
            ],
            'create directories when some exist' => [
                'expected' => 'OK',
                'existingDirectories' => [
                    '/app/public/upload/post',
                    '/app/public/upload/post_section',
                ],
                'directoriesToCreate' => [
                    '/app/public/upload/category',
                ],
                'arguments' => [],
            ],
            'do not create directories when all exist' => [
                'expected' => 'OK',
                'existingDirectories' => [
                    '/app/public/upload/category',
                    '/app/public/upload/post',
                    '/app/public/upload/post_section',
                ],
                'directoriesToCreate' => [],
                'arguments' => [],
            ],
            'clear directories with clear option' => [
                'expected' => 'OK',
                'existingDirectories' => [
                    '/app/public/upload/category',
                    '/app/public/upload/post',
                    '/app/public/upload/post_section',
                ],
                'directoriesToCreate' => [],
                'arguments' => ['--clear' => true],
            ],
        ];
    }

    #[DataProvider('getUploadDirsDataProvider')]
    public function testExecuteCreateDirectories(
        string $expected,
        array $existingDirectories,
        array $directoriesToCreate,
        array $arguments,
    ): void {
        self::bootKernel();

        $commandTester = new CommandTester($this->command);

        $this->setupExistsMock($existingDirectories);

        if ([] !== $arguments) {
            $this->setupClearMocks($existingDirectories);
        } else {
            $this->setupCreateMocks($directoriesToCreate);
        }

        $commandTester->execute($arguments);
        $commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString($expected, $commandTester->getDisplay());
    }

    private function setupExistsMock(array $existingDirectories): void
    {
        $this->fileSystem
            ->shouldReceive('exists')
            ->with(Mockery::any())
            ->andReturnUsing(function ($dir) use ($existingDirectories) {
                return in_array($dir, $existingDirectories, true);
            });
    }

    private function setupCreateMocks(array $directoriesToCreate): void
    {
        if ([] === $directoriesToCreate) {
            $this->fileSystem
                ->shouldReceive('mkdir')
                ->never();
        } else {
            $this->fileSystem
                ->shouldReceive('mkdir')
                ->times(count($directoriesToCreate))
                ->with(Mockery::any(), Mockery::any())
                ->andReturnUsing(function ($dir) use ($directoriesToCreate) {
                    return in_array($dir, $directoriesToCreate, true);
                });
        }
    }

    private function setupClearMocks(array $existingDirectories): void
    {
        $this->fileSystem
            ->shouldReceive('remove')
            ->times(count($existingDirectories))
            ->with(Mockery::any())
            ->andReturnUsing(function ($dir) use ($existingDirectories) {
                return in_array($dir, $existingDirectories, true);
            });

        $this->fileSystem
            ->shouldReceive('mkdir')
            ->times(count($existingDirectories))
            ->with(Mockery::any(), Mockery::any())
            ->andReturnUsing(function ($dir) use ($existingDirectories) {
                return in_array($dir, $existingDirectories, true);
            });
    }

    protected function setUp(): void
    {
        $this->fileSystem = Mockery::mock(Filesystem::class);
        $this->command = new CreateUploadDirsCommand(
            $this->fileSystem,
            self::getContainer()->get(ParameterBagInterface::class)
        );

        parent::setUp();
    }
}
