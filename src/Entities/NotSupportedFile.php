<?php

declare(strict_types=1);

namespace SortingPhotosByDate\Entities;

use SplFileInfo;
use Carbon\Carbon;
use SortingPhotosByDate\Contracts\FileInterface;

final class NotSupportedFile implements FileInterface
{
    private string $name;
    private string $extension;
    private Carbon $dateTime;
    private string $type;
    public const TYPE = 'not_supported';

    public function __construct(string $filePath)
    {
        $fileInfo = new SplFileInfo($filePath);
        list($type) = explode('/', (string) mime_content_type($filePath));

        $this->type = $type;
        $this->name = $fileInfo->getBasename();
        $this->extension = $fileInfo->getExtension();

        if (preg_match('/(?<dateTime>\d{8})/iu', $filePath, $matches)) {
            $this->dateTime = Carbon::parse($matches['dateTime']);
        } else {
            $this->dateTime = Carbon::parse((string) $fileInfo->getMTime());
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @return Carbon
     */
    public function getDateTime(): Carbon
    {
        return $this->dateTime;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
