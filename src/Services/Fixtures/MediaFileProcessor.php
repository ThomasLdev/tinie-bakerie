<?php

declare(strict_types=1);

namespace App\Services\Fixtures;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class MediaFileProcessor
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    public function process(string $fileName): ?File
    {
        $sourceFilePath = sprintf('fixtures/media/%s', $fileName);
        $tempFilePath = sprintf('%s/%s-%s', sys_get_temp_dir(), uniqid('', true), $fileName);
        $this->filesystem->copy($sourceFilePath, $tempFilePath);

        return new UploadedFile(
            $tempFilePath,
            $fileName,
            null,
            null,
            true
        );
    }
}
