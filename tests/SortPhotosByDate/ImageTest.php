<?php

declare(strict_types=1);

namespace SortPhotosByDate;

use Carbon\Carbon;
use ReflectionClass;
use SortPhotosByDate\Image;
use PHPUnit\Framework\TestCase;
use SortPhotosByDate\Exception\SortPhotosException;

class ImageTest extends TestCase
{
    private Image $image;

    private string $name = 'file-2021-09-28.jpg';
    private string $extension ='jpeg';
    private Carbon $dateTime;

    protected function setUp(): void
    {
        $this->dateTime = Carbon::parse('2021-09-28');
        $this->image = new Image(__DIR__ . '/../source-files/file-2021-09-28.jpg');
    }

    public function testGetDateTime()
    {
        $dateTime = $this->image->getDateTime();
        $this->assertEquals($this->dateTime->toDateString(), $dateTime->toDateString());
    }

    public function testGetExtension()
    {
        $extention = $this->image->getExtension();
        $this->assertEquals($this->extension, $extention);
    }

    public function testGetName()
    {
        $name = $this->image->getName();
        $this->assertEquals($this->name, $name);
    }

    public function testGetType()
    {
        $type = $this->image->getType();
        $this->assertEquals(Image::TYPE, $type);
    }

    public function testInvalidFile()
    {
        $this->markTestSkipped('There is no file available without metadata');
        $invalidFile = __DIR__ . '/../source-files/no_exif.jpg';

        $reflection = new ReflectionClass(SortPhotosException::class);
        /**
         * @psalm-var string $message
         */
        $message = $reflection->getConstant('FAILED_EXTRACT_METADATA');

        $this->expectException(SortPhotosException::class);
        $this->expectErrorMessage(sprintf($message, $invalidFile));

        new Image($invalidFile);
    }
}
