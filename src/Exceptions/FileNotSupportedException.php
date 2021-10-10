<?php

declare(strict_types=1);

namespace SortingPhotosByDate\Exceptions;

use RuntimeException;

final class FileNotSupportedException extends RuntimeException
{
    private const FILE_TYPE_NOT_SUPPORTED = 'The file type  %s is not supported';

    public function byType(string $type): self
    {
        return new self(
            sprintf(
                self::FILE_TYPE_NOT_SUPPORTED,
                $type
            )
        );
    }
}
