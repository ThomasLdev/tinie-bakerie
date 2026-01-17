<?php

declare(strict_types=1);

namespace App\Services\Fixtures;

use JoliCode\MediaBundle\Library\LibraryContainer;
use JoliCode\MediaBundle\Model\Media;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MediaLoader
{
    private const string FIXTURES_PATH = '/assets/fixtures/media/';

    /** @var string[]|null */
    private ?array $files = null;

    /** @var array<string, Media> */
    private array $mediaCache = [];

    public function __construct(
        private readonly LibraryContainer $libraryContainer,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function getRandomMedia(): Media
    {
        $file = $this->getRandomFile();

        if (isset($this->mediaCache[$file])) {
            return $this->mediaCache[$file];
        }

        $sourcePath = $this->projectDir . self::FIXTURES_PATH . $file;
        $content = file_get_contents($sourcePath);

        if (false === $content) {
            throw new \RuntimeException(sprintf('Cannot read fixture file: %s', $sourcePath));
        }

        $storagePath = sprintf('fixtures/%s', $file);

        $media = $this->libraryContainer
            ->getDefault()
            ->getOriginalStorage()
            ->createMedia($storagePath, $content);

        $this->mediaCache[$file] = $media;

        return $media;
    }

    private function getRandomFile(): string
    {
        if (null === $this->files) {
            $fullPath = $this->projectDir . self::FIXTURES_PATH;
            $files = array_diff(scandir($fullPath) ?: [], ['.', '..']);
            $this->files = array_values(array_filter(
                $files,
                static fn(string $f): bool => is_file($fullPath . $f)
            ));

            if ([] === $this->files) {
                throw new \RuntimeException('No fixture media files found');
            }
        }

        return $this->files[array_rand($this->files)];
    }
}
