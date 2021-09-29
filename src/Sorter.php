<?php

declare(strict_types=1);

namespace SortingPhotosByDate;

use SortingPhotosByDate\Exception\SortingPhotosException;

final class Sorter
{
    private const PERMISSIONS = 0777;

    private string $copyToDirectory;
    private string $catalogUnsortedPhotos;

    public function __construct(
        string $catalogUnsortedPhotos,
        string $copyToDirectory
    ) {
        if (!is_dir($catalogUnsortedPhotos)) {
            throw SortingPhotosException::noSuchDirectory($catalogUnsortedPhotos);
        }

        $this->makeDirIfNotExist($copyToDirectory);
        $this->copyToDirectory = $copyToDirectory;
        $this->catalogUnsortedPhotos = $catalogUnsortedPhotos;
    }

    public function process(): bool
    {
        foreach ($this->getFiles() as $fileName) {
            try {
                $filePath = $this->getFilePath($fileName);

                $file = exif_imagetype($filePath)
                    ? new Image($filePath)
                    : new Video($filePath);

                $this->copyFile($file, $filePath);
            } catch (SortingPhotosException $exception) {
                printf("[%s] %s \n", $exception::class, $exception->getMessage());
            }
        }

        return true;
    }

    /**
     * @psalm-return array<int, string>
     */
    private function getFiles(): array
    {
        $files = scandir($this->catalogUnsortedPhotos);
        if (false === $files) {
            throw SortingPhotosException::directoryIsEmpty($this->catalogUnsortedPhotos);
        }

        $files = array_filter($files, fn (string $file) => !in_array($file, ['.', '..', '.DS_Store', '.temp'], true));
        if (0 === count($files)) {
            throw SortingPhotosException::directoryIsEmpty($this->catalogUnsortedPhotos);
        }

        return $files;
    }

    private function makeDirIfNotExist(string $dir): void
    {
        if (is_dir($dir)) {
            $this->checkPermissionsDirAndIfNotAdd($dir);

            return;
        }

        if (!mkdir($dir, self::PERMISSIONS, true)) {
            throw SortingPhotosException::failedCreateFolder($dir);
        }
    }

    private function checkPermissionsDirAndIfNotAdd(string $dir): void
    {
        $permissions = fileperms($dir);
        if (self::PERMISSIONS !== substr(sprintf('%o', $permissions), -4)) {
            chmod($dir, self::PERMISSIONS);
        }
    }

    private function copyFile(FileInterface $file, string $sourceFile): void
    {
        $copyToDir = sprintf(
            '%s/%s/%s/%s',
            $this->copyToDirectory,
            $file->getType(),
            $file->getDateTime()->year,
            $file->getDateTime()->format('Y-m')
        );

        $this->makeDirIfNotExist($copyToDir);

        $newFile = sprintf('%s/%s', $copyToDir, $file->getName());
        if (file_exists($newFile)) {
            throw SortingPhotosException::fileExists($newFile);
        }

        if (!copy($sourceFile, $newFile)) {
            throw SortingPhotosException::notCopyFile($file->getName());
        }
    }

    private function getFilePath(string $fileName): string
    {
        return sprintf(
            '%s/%s',
            $this->catalogUnsortedPhotos,
            $fileName
        );
    }
}
