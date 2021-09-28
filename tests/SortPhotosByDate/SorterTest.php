<?php

declare(strict_types=1);

namespace SortPhotosByDate;

use ReflectionClass;
use PHPUnit\Framework\TestCase;
use SortPhotosByDate\Exception\FileSystemException;

class SorterTest extends TestCase
{
    public function testOfNonExistentDirectoryWithFiles(): void
    {
        $dir = __DIR__.'/../a-non-existent-directory';
        $copyToDir = __DIR__.'/../copy-directory';

        $reflection = new ReflectionClass(FileSystemException::class);
        $message = $reflection->getConstant('NO_SUCH_DIRECTORY');

        $this->expectException(FileSystemException::class);
        $this->expectErrorMessage(sprintf($message, $dir));

        new Sorter($dir, $copyToDir);
    }

    public function testProcess()
    {
        $dir = __DIR__.'/../source-files';
        $copyToDir = __DIR__.'/../copy-directory';

        $sorter = new Sorter($dir, $copyToDir);
        $result = $sorter->process();

        $this->assertTrue($result);
        $this->assertDirectoryExists($copyToDir);
        $this->assertDirectoryIsReadable($copyToDir);
        $this->rmdir($copyToDir);
    }

    public function testEmptyDirectory(): void
    {
        $dir = __DIR__.'/../empty-dir';
        $copyToDir = __DIR__.'/../copy-directory';

        $reflection = new ReflectionClass(FileSystemException::class);
        $message = $reflection->getConstant('DIRECTORY_IS_EMPTY');

        $this->expectException(FileSystemException::class);
        $this->expectErrorMessage(sprintf($message, $dir));

        try {
            (new Sorter($dir, $copyToDir))->process();
        } catch (Throwable $throwable) {
            $this->rmdir($dir);
            $this->rmdir($copyToDir);
            throw $throwable;
        }
    }

    private function rmdir($dir): void
    {
        $files = array_filter(scandir($dir), fn (string $file) => !in_array($file, ['.', '..', '.DS_Store', '.temp'], true));
        foreach ($files as $file) {
            $filePath = sprintf('%s/%s', $dir, $file);
            is_dir($filePath) ? $this->rmdir($filePath) : unlink($filePath);
        }
        rmdir($dir);
    }
}
