<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('memory_limit', '550M');
ini_set('display_startup_errors', '1');

require_once __DIR__.'/vendor/autoload.php';

use SortPhotosByDate\Sorter;

try {
    $unsortedPhotosDir = __DIR__.'/fotos2';
    $copyToDir = __DIR__.'/sorted-fotos2';

    $sorter = new Sorter($unsortedPhotosDir, $copyToDir);

    $sorter->process();
} catch (Throwable $throwable) {
    dump($throwable);
}
