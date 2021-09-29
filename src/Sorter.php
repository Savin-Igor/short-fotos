<?php

declare(strict_types=1);

namespace SortPhotosByDate;

use Exception;
use SortPhotosByDate\Exception\SortPhotosException;

final class Sorter
{
    private const PERMISSIONS = 0777;
    private string $catalogUnsortedPhotos;
    private string $copyToDirectory;

    public function __construct(
        string $catalogUnsortedPhotos,
        string $copyToDirectory
    ) {
        if (!is_dir($catalogUnsortedPhotos)) {
            throw SortPhotosException::noSuchDirectory($catalogUnsortedPhotos);
        }

        $this->catalogUnsortedPhotos = $catalogUnsortedPhotos;

        $this->makeDirIfNotExist($copyToDirectory);
        $this->copyToDirectory = $copyToDirectory;
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
            } catch (Exception $exception) {
                printf("[%s] %s \n", $exception::class, $exception->getMessage());
            }
        }

        return true;
    }

    /**
     * @return array<int, string>
     */
    private function getFiles(): array
    {
        $files = scandir($this->catalogUnsortedPhotos);
        if (false === $files) {
            throw SortPhotosException::directoryIsEmpty($this->catalogUnsortedPhotos);
        }

        $files = array_filter($files, fn (string $file) => !in_array($file, ['.', '..', '.DS_Store', '.temp'], true));
        if (0 === count($files)) {
            throw SortPhotosException::directoryIsEmpty($this->catalogUnsortedPhotos);
        }

        return $files;
    }

    private function makeDirIfNotExist(string $dir): void
    {
        if (is_dir($dir)) {
            $permissions = fileperms($dir);
            if (self::PERMISSIONS !== substr(sprintf('%o', $permissions), -4)) {
                chmod($dir, self::PERMISSIONS);
            }

            return;
        }

        if (!mkdir($dir, self::PERMISSIONS, true)) {
            throw SortPhotosException::failedCreateFolder($dir);
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
            throw SortPhotosException::fileExists($newFile);
        }

        if (!copy($sourceFile, $newFile)) {
            throw SortPhotosException::notCopyFile($file->getName());
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
