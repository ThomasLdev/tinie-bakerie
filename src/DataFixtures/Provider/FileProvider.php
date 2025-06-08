<?php

namespace App\DataFixtures\Provider;

use Faker\Generator;
use Faker\Provider\Base as BaseProvider;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileProvider extends BaseProvider
{
    public function __construct(private readonly Filesystem $filesystem, Generator $generator)
    {
        parent::__construct($generator);
    }

    public function file(?string $specificValue = null): ?File
    {
        if (null === $specificValue) {
            return null;
        }

        $sourceFilePath = sprintf('fixtures/media/%s', $specificValue);
        $tempFilePath = sprintf('%s/%s-%s', sys_get_temp_dir(), uniqid('', true), $specificValue);
        $this->filesystem->copy($sourceFilePath, $tempFilePath);

        return new UploadedFile(
            $tempFilePath,
            $specificValue,
            null,
            null,
            true
        );
    }
}
