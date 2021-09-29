### Sorting Photos By Date

We take a catalog with photos and videos. We indicate where we want to move it. Run the script and voila! All files are beautifully sorted by years and months.


##### Example

Sample code from the index php file (Run from the console - php index.php):

```php
<?php

declare(strict_types=1);

use SortingPhotosByDate\Sorter;

require_once __DIR__.'/vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('memory_limit', '550M');
ini_set('display_startup_errors', '1');

$unsortedPhotosDir = __DIR__.'/fotos';
$copyToDir = __DIR__.'/sorted-fotos';

try {
    $sorter = new Sorter($unsortedPhotosDir, $copyToDir);
    $sorter->process();
} catch (Throwable $throwable) {
    printf("[%s] %s \n", $throwable::class, $throwable->getMessage());
}

```
