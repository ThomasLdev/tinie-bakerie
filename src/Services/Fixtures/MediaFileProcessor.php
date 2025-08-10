<?php

declare(strict_types=1);

namespace App\Services\Fixtures;

use App\Entity\Contracts\MediaEntityInterface;
use App\Services\Media\Enum\MediaType;
use Gedmo\Uploadable\MimeType\MimeTypeGuesser;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaFileProcessor
{
    private const string FOLDER_PATH = __DIR__ . '/media/';

    private bool $imageOnly = false;

    public function __construct(private readonly Filesystem $filesystem)
    {
    }

    public function hasImageOnly(): self
    {
        $this->imageOnly = true;

        return $this;
    }

    public function process(MediaEntityInterface $entity): void
    {
        $fileName = $this->getRandomFileName();
        $sourceFilePath = sprintf('%s/%s', self::FOLDER_PATH, $fileName);
        $tempFilePath = sprintf('%s/%s-%s', sys_get_temp_dir(), uniqid('', true), $fileName);
        $this->filesystem->copy($sourceFilePath, $tempFilePath);

        $file = new UploadedFile(
            $tempFilePath,
            $fileName,
            new MimeTypeGuesser()->guess($sourceFilePath),
            null,
            true
        );

        $entity->setMediaName($fileName);
        $entity->setType($this->getFileType($fileName));
        $entity->setMediaFile($file);
    }

    private function getRandomFileName(): string
    {
        $files = array_diff(scandir(self::FOLDER_PATH), ['.', '..']);
        $files = array_filter($files, static fn($file) => is_file(self::FOLDER_PATH . '/' . $file));

        if (empty($files)) {
            throw new \RuntimeException('No media files found in ./media/ directory');
        }

        if ($this->imageOnly) {
            $files = array_filter($files, fn($file) => $this->getFileType($file) === MediaType::Image);
        }

        return $files[array_rand($files)];
    }

    private function getFileType(string $fileName): MediaType
    {
        return MediaType::fromExtension(pathinfo($fileName, PATHINFO_EXTENSION));
    }
}
