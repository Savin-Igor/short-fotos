<?php

declare(strict_types=1);

namespace SortingPhotosByDate\Tests\Entities;

use Carbon\Carbon;
use ReflectionClass;
use PHPUnit\Framework\TestCase;
use SortingPhotosByDate\Entities\Image;
use SortingPhotosByDate\Exceptions\SortingPhotosException;

final class ImageTest extends TestCase
{
    private Image $image;
    private Carbon $dateTime;

    private string $extension = 'jpeg';
    private string $name = 'file-2021-09-28.jpg';

    protected function setUp(): void
    {
        $file = getenv('DIRECTORY_OF_TEST_FILES').'/test-files/file-2021-09-28.jpg';
        $this->dateTime = Carbon::parse('2021-09-30');
        $this->image = new Image($file);
    }

    public function testGetDateTime(): void
    {
        $dateTime = $this->image->getDateTime();
        $this->assertEquals($this->dateTime->toDateString(), $dateTime->toDateString());
    }

    public function testGetExtension(): void
    {
        $extention = $this->image->getExtension();
        $this->assertEquals($this->extension, $extention);
    }

    public function testGetName(): void
    {
        $name = $this->image->getName();
        $this->assertEquals($this->name, $name);
    }

    public function testGetType(): void
    {
        $type = $this->image->getType();
        $this->assertEquals(Image::TYPE, $type);
    }

    public function testInvalidFile(): void
    {
        $this->markTestSkipped('There is no file available without metadata');
        $invalidFile = getenv('DIRECTORY_OF_TEST_FILES').'/no_exif.jpg';

        $reflection = new ReflectionClass(SortingPhotosException::class);
        /**
         * @psalm-var string $message
         */
        $message = $reflection->getConstant('FAILED_EXTRACT_METADATA');

        $this->expectException(SortingPhotosException::class);
        $this->expectErrorMessage(sprintf($message, $invalidFile));

        new Image($invalidFile);
    }
}
