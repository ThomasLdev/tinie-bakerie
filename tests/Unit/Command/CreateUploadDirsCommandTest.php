<?php

namespace App\Tests\Unit\Command;

use App\Command\CreateUploadDirsCommand;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
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
                    '/public/upload/category',
                    '/public/upload/post',
                    '/public/upload/post_section',
                ],
                'arguments' => [],
            ],
            'create directories when some exist' => [
                'expected' => 'OK',
                'existingDirectories' => [
                    '/public/upload/post',
                    '/public/upload/post_section',
                ],
                'directoriesToCreate' => [
                    '/public/upload/category',
                    '/public/upload/post',
                    '/public/upload/post_section',
                ],
                'arguments' => [],
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

        $this->fileSystem->shouldReceive('exists')
            ->with(Mockery::any())
            ->andReturnUsing(function ($dir) use ($existingDirectories) {
                return in_array($dir, $existingDirectories, true);
            });

        $this->fileSystem->shouldReceive('mkdir')
            ->with(Mockery::any(), Mockery::any())
            ->andReturnUsing(function ($dir) use ($directoriesToCreate) {
                if (in_array($dir, $directoriesToCreate, true)) {
                    return true;
                }

                return false;
            });

        $commandTester->execute($arguments);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString($expected, $output);
    }

    protected function setUp(): void
    {
        $this->fileSystem = Mockery::mock(Filesystem::class);
        $this->command = new CreateUploadDirsCommand($this->fileSystem, '/tmp/test_project_dir');

        parent::setUp();
    }
}
