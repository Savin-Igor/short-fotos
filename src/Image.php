<?php

declare(strict_types=1);

namespace SortPhotosByDate;

use Carbon\Carbon;

use InvalidArgumentException;

final class Image implements FileInterface
{
    private string $name;
    private string $extension;
    private Carbon $dateTime;
    public const TYPE = 'image';

    public function __construct(string $filePath)
    {
        $exif = exif_read_data($filePath, '0', true);

        if (false === $exif || false === array_key_exists('FILE', $exif)) {
            throw new InvalidArgumentException("Failed to extract file {$filePath} metadata");
        }

        $this->name = $exif['FILE']['FileName'];

        list(, $extension) = explode('/', $exif['FILE']['MimeType']);
        $this->extension = $extension;

        if (preg_match('/(?<dateTime>\d{8})/iu', $filePath, $matches)) {
            $this->dateTime = Carbon::parse($matches['dateTime']);
        } else {
            $this->dateTime = Carbon::parse($exif['FILE']['FileDateTime']);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getDateTime(): Carbon
    {
        return $this->dateTime;
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
