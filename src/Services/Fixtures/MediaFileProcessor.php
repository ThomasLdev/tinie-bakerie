<?php

declare(strict_types=1);

namespace App\Services\Fixtures;

use App\Entity\Contracts\MediaEntityInterface;
use App\Services\Media\Enum\MediaType;
use Gedmo\Uploadable\MimeType\MimeTypeGuesser;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class MediaFileProcessor
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    public function process(MediaEntityInterface $entity, string $folderPath): void
    {
        $fileName = $this->getRandomFileName($folderPath);
        $sourceFilePath = sprintf(__DIR__ . '/media/%s/%s', $folderPath, $fileName);
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

    private function getRandomFileName(string $folderPath): string
    {
        $mediaDir = __DIR__ . '/media/' . $folderPath;
        $files = array_diff(scandir($mediaDir), ['.', '..']);
        $files = array_filter($files, static fn($file) => is_file($mediaDir . '/' . $file));

        if (empty($files)) {
            throw new \RuntimeException('No media files found in fixtures/media/posts directory');
        }

        return $files[array_rand($files)];
    }

    private function getFileType(string $fileName): MediaType
    {
        return MediaType::fromExtension(pathinfo($fileName, PATHINFO_EXTENSION));
    }
}
