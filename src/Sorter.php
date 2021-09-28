<?php

declare(strict_types=1);

namespace SortPhotosByDate;

use Exception;
use SortPhotosByDate\Exception\FileSystemException;

final class Sorter
{
    private string $catalogUnsortedPhotos;
    private string $copyToDirectory;

    public function __construct(
        string $catalogUnsortedPhotos,
        string $copyToDirectory
    ) {
        if (!is_dir($catalogUnsortedPhotos)) {
            throw FileSystemException::noSuchDirectory($catalogUnsortedPhotos);
        }

        $this->catalogUnsortedPhotos = $catalogUnsortedPhotos;

        if (!is_dir($copyToDirectory)) {
            $this->makeDir($copyToDirectory);
        }

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
                printf("%s \n", $exception->getMessage());
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
            throw FileSystemException::directoryIsEmpty($this->catalogUnsortedPhotos);
        }

        $files = array_filter($files, fn (string $file) => !in_array($file, ['.', '..', '.DS_Store', '.temp'], true));
        if (0 === count($files)) {
            throw FileSystemException::directoryIsEmpty($this->catalogUnsortedPhotos);
        }

        return $files;
    }

    private function makeDir(string $dir): bool
    {
        if (is_dir($dir)) {
            return true;
        }

        if (!mkdir($dir, 0777, true)) {
            throw FileSystemException::failedCreateFolder($dir);
        }

        return true;
    }

    private function copyFile(FileInterface $file, string $sourceFile): bool
    {
        $copyToDir = sprintf(
            '%s/%s/%s/%s',
            $this->copyToDirectory,
            $file->getType(),
            $file->getDateTime()->year,
            $file->getDateTime()->format('Y-m')
        );

        if (!is_dir($copyToDir)) {
            $this->makeDir($copyToDir);
        }

        $newFile = sprintf('%s/%s', $copyToDir, $file->getName());
        if (file_exists($newFile)) {
            throw FileSystemException::fileExists($newFile);
        }

        if (!copy($sourceFile, $newFile)) {
            throw FileSystemException::notCopyFile($file->getName());
        }

        return true;
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
