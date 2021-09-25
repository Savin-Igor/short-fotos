<?php

declare(strict_types=1);

namespace SortPhotosByDate;

use Exception;
use RuntimeException;

final class Sorter
{
    private string $catalogUnsortedPhotos;
    private string $copyToDirectory;

    public function __construct(
        string $catalogUnsortedPhotos,
        string $copyToDirectory
    ) {
        if (!is_dir($catalogUnsortedPhotos)) {
            throw new RuntimeException("There {$catalogUnsortedPhotos} is no such directory");
        }

        $this->catalogUnsortedPhotos = $catalogUnsortedPhotos;

        if (!is_dir($copyToDirectory)) {
            $this->makeDir($copyToDirectory);
        }

        $this->copyToDirectory = $copyToDirectory;
    }

    public function process(): bool
    {
        $files = $this->getFiles();

        dump('All files: '.count($files));
        dump('Dist '.exec("find {$this->catalogUnsortedPhotos} -type f | wc -l"));

        foreach ($this->getFiles() as $index => $fileName) {
            try {
                $filePath = $this->getFilePath($fileName);

                $file = exif_imagetype($filePath)
                    ? new Image($filePath)
                    : new Video($filePath);

                $this->copyFile($file, $filePath);
            } catch (Exception $exception) {
                dump($exception->getMessage());
            }
        }

        dump('END!');
        return true;
    }

    /**
     * @return array<int, string>
     */
    private function getFiles(): array
    {
        $files = scandir($this->catalogUnsortedPhotos);

        if (false === $files || 0 === count($files)) {
            throw new RuntimeException("The directory {$this->catalogUnsortedPhotos} is empty");
        }

        return array_filter($files, fn (string $file) => !in_array($file, ['.', '..', '.DS_Store', '.temp'], true));
    }

    private function makeDir(string $dir): bool
    {
        if (is_dir($dir)) {
            return true;
        }

        if (!mkdir($dir, 0777, true)) {
            throw new RuntimeException("Failed to create a folder {$dir}");
        }

        return true;
    }

    private function copyFile(FileInterface $file, string $sourceFile): bool
    {
        $copyToDir = sprintf(
            '%s/%s/%s',
            $this->copyToDirectory,
            $file->getType(),
            $file->getDateTime()->format('Y-m')
        );

        if (!is_dir($copyToDir)) {
            $this->makeDir($copyToDir);
        }

        $newFile = sprintf('%s/%s', $copyToDir, $file->getName());
        if (file_exists($newFile)) {
            throw new RuntimeException("The file {$newFile} already exists");
        }

        if (!copy($sourceFile, $newFile)) {
            throw new RuntimeException("Couldn't copy the file {$file->getName()}");
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
