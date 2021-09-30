<?php

declare(strict_types=1);

namespace SortingPhotosByDate\Tests\Services;

use Exception;
use ReflectionClass;
use PHPUnit\Framework\TestCase;
use SortingPhotosByDate\Services\Sorter;
use SortingPhotosByDate\Exceptions\SortingPhotosException;

final class SorterTest extends TestCase
{
    public function testOfNonExistentDirectoryWithFiles(): void
    {
        $dir = getenv('DIRECTORY_OF_TEST_FILES').'/a-non-existent-directory';
        $copyToDir = getenv('DIRECTORY_OF_TEST_FILES').'/copy-directory';

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
        $dir = getenv('DIRECTORY_OF_TEST_FILES').'/test-files';
        $copyToDir = getenv('DIRECTORY_OF_TEST_FILES').'/copy-directory';

        $sorter = new Sorter($dir, $copyToDir);
        $result = $sorter->process();

        $this->assertTrue($result);
        $this->assertDirectoryExists($copyToDir);
        $this->assertDirectoryIsReadable($copyToDir);
        $this->rmDir($copyToDir);
    }

    public function testEmptyDirectory(): void
    {
        $dir = getenv('DIRECTORY_OF_TEST_FILES').'/empty-dir';
        $copyToDir = getenv('DIRECTORY_OF_TEST_FILES').'/copy-directory';

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
            $this->rmDir($dir);
            $this->rmDir($copyToDir);
            throw $exception;
        }
    }

    private function rmDir(string $dir): void
    {
        $files = array_filter(scandir($dir), fn (string $file) => !in_array($file, ['.', '..', '.DS_Store', '.temp'], true));
        foreach ($files as $file) {
            $filePath = sprintf('%s/%s', $dir, $file);
            is_dir($filePath) ? $this->rmDir($filePath) : unlink($filePath);
        }
        rmdir($dir);
    }
}
