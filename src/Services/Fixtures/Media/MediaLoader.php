<?php

declare(strict_types=1);

namespace App\Services\Fixtures\Media;

use App\Services\Fixtures\Media\Model\FileModel;
use App\Services\Media\Enum\MediaType;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

class MediaLoader
{
    private const string FOLDER_PATH = '/assets/fixtures/media/';

    /** @var array<array-key, string> */
    private array $files = [];

    public function __construct(
        private readonly Filesystem $filesystem,
        #[Autowire('%kernel.project_dir%')] private readonly string $rootDir,
    ) {
    }

    /**
     * @return array{mediaName: string, mediaFile: UploadedFile, type: MediaType}
     */
    public function getRandomMediaFactoryFields(): array
    {
        $media = $this->createRandomFileModel();

        return [
            'mediaName' => $media->getName(),
            'mediaFile' => $media->getFile(),
            'type' => $media->getType(),
        ];
    }

    private function createRandomFileModel(): FileModel
    {
        $files = $this->getFiles();
        $file = $files[array_rand($files)];

        return new FileModel(
            $file,
            $this->getUploadedFile($file, $this->rootDir.self::FOLDER_PATH.$file),
            $this->getFileType($file)
        );
    }

    /** @return array<array-key, string> */
    private function getFiles(): array
    {
        if ([] !== $this->files) {
            return $this->files;
        }

        $fullPath = $this->rootDir.self::FOLDER_PATH;
        $files = array_diff(scandir($fullPath), ['.', '..']);
        $files = array_filter($files, static fn ($fileName) => is_file($fullPath.$fileName));

        if ([] === $files) {
            throw new RuntimeException(sprintf('No media files found in %s directory', $fullPath));
        }

        return $files;
    }

    private function getUploadedFile(string $fileName, string $fileFullPath): UploadedFile
    {
        $tempFilePath = sprintf(
            '%s/%s-%s',
            sys_get_temp_dir(),
            md5($fileName.microtime()),
            $fileName
        );

        if (!file_exists($fileFullPath) || !is_readable($fileFullPath)) {
            throw new RuntimeException(sprintf('Source file "%s" does not exist or is not readable', $fileFullPath));
        }

        $this->filesystem->copy($fileFullPath, $tempFilePath, true);
        $this->filesystem->chmod($tempFilePath, 0644);

        if (!file_exists($tempFilePath) || !is_readable($tempFilePath)) {
            throw new RuntimeException(sprintf('Failed to create temporary file at "%s"', $tempFilePath));
        }

        return new UploadedFile(
            $tempFilePath,
            $fileName,
            $this->guessMimeType($tempFilePath),
            null,
            true
        );
    }

    private function getFileType(string $fileName): MediaType
    {
        return MediaType::fromExtension(pathinfo($fileName, PATHINFO_EXTENSION));
    }

    private function guessMimeType(string $filePath): string
    {
        $mimeType = new MimeTypes()->guessMimeType($filePath);

        if (null === $mimeType) {
            throw new RuntimeException(sprintf('Could not guess MIME type for file "%s"', $filePath));
        }

        return $mimeType;
    }
}
