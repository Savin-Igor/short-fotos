<?php

declare(strict_types=1);

namespace SortingPhotosByDate;

use Carbon\Carbon;

interface FileInterface
{
    public function getName(): string;

    public function getExtension(): string;

    public function getDateTime(): Carbon;

    public function getType(): string;
}
