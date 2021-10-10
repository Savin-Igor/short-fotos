<?php

declare(strict_types=1);

namespace SortingPhotosByDate\Services;

use SplFileInfo;
use FilesystemIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use SortingPhotosByDate\Entities\Image;
use SortingPhotosByDate\Entities\Video;
use SortingPhotosByDate\Entities\NotSupportedFile;
use SortingPhotosByDate\Contracts\FileInterface;
use SortingPhotosByDate\Exceptions\SortingPhotosException;
use SortingPhotosByDate\Exceptions\FileNotSupportedException;

final class Sorter
{
    private const PERMISSIONS = 0744;

    private string $copyToDirectory;
    private string $catalogUnsortedPhotos;

    public function __construct(
        string $catalogUnsortedPhotos,
        string $copyToDirectory
    ) {
        if (!is_dir($catalogUnsortedPhotos)) {
            throw SortingPhotosException::noSuchDirectory($catalogUnsortedPhotos);
        }

        $this->copyToDirectory = $copyToDirectory;
        $this->catalogUnsortedPhotos = $catalogUnsortedPhotos;
    }

    public function process(): bool
    {
        $flags = FilesystemIterator::NEW_CURRENT_AND_KEY | FilesystemIterator::SKIP_DOTS;
        $directoryIterator = new RecursiveDirectoryIterator($this->catalogUnsortedPhotos, $flags);
        $recursiveIterator = new RecursiveIteratorIterator($directoryIterator);

        $fileCount = iterator_count($recursiveIterator);
        printf('All files count %d', $fileCount);
        match ($fileCount) {
            0 => throw SortingPhotosException::directoryIsEmpty($this->catalogUnsortedPhotos),
            default => $this->makeDirIfNotExist($this->copyToDirectory),
        };

        $recursiveIterator->rewind();
        while ($recursiveIterator->valid()) {
            /**
             * @var SplFileInfo $fileInfo
             */
            $fileInfo = $recursiveIterator->current();

            $filePath = $fileInfo->getPathname();
            list($type) = explode('/', (string) mime_content_type($filePath));
            try {
                $file = match ($type) {
                    Image::TYPE => new Image($filePath),
                    Video::TYPE => new Video($filePath),
                    default => throw FileNotSupportedException::byType($type),
                };

                $this->copyFile($file, $filePath);
            } catch (FileNotSupportedException $exception) {
                $this->copyFile(new NotSupportedFile($filePath), $filePath);
                printf("[%s] %s \n", $exception::class, $exception->getMessage());
            } catch (SortingPhotosException $exception) {
                printf("[%s] %s \n", $exception::class, $exception->getMessage());
            }

            $directoryIterator->next();
        }

        return (bool) $fileCount;
    }

    private function makeDirIfNotExist(string $dir): void
    {
        if (is_dir($dir)) {
            $this->setPermissions($dir);

            return;
        }

        if (!mkdir($dir, self::PERMISSIONS, true)) {
            throw SortingPhotosException::failedCreateFolder($dir);
        }
    }

    private function setPermissions(string $dir): void
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
            $file->getDateTime()->month
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
}
