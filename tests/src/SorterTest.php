<?php

declare(strict_types=1);

namespace SortingPhotosByDate\Tests;

use Exception;
use ReflectionClass;
use SortingPhotosByDate\Sorter;
use PHPUnit\Framework\TestCase;
use SortingPhotosByDate\Exception\SortingPhotosException;

final class SorterTest extends TestCase
{
    public function testOfNonExistentDirectoryWithFiles(): void
    {
        $dir = __DIR__.'/../a-non-existent-directory';
        $copyToDir = __DIR__.'/../copy-directory';

        $reflection = new ReflectionClass(SortingPhotosException::class);
        /**
         * @psalm-var string $message
         */
        $message = $reflection->getConstant('NO_SUCH_DIRECTORY');

        $this->expectException(SortingPhotosException::class);
        $this->expectErrorMessage(sprintf($message, $dir));

        new Sorter($dir, $copyToDir);
    }

    public function testProcess(): void
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

        mkdir($dir, 0777);
        $reflection = new ReflectionClass(SortingPhotosException::class);
        /**
         * @psalm-var string $message
         */
        $message = $reflection->getConstant('DIRECTORY_IS_EMPTY');

        $this->expectException(SortingPhotosException::class);
        $this->expectErrorMessage(sprintf($message, $dir));

        try {
            (new Sorter($dir, $copyToDir))->process();
        } catch (Exception $exception) {
            $this->rmdir($dir);
            $this->rmdir($copyToDir);
            throw $exception;
        }
    }

    private function rmdir(string $dir): void
    {
        $files = array_filter(scandir($dir), fn (string $file) => !in_array($file, ['.', '..', '.DS_Store', '.temp'], true));
        foreach ($files as $file) {
            $filePath = sprintf('%s/%s', $dir, $file);
            is_dir($filePath) ? $this->rmdir($filePath) : unlink($filePath);
        }
        rmdir($dir);
    }
}
