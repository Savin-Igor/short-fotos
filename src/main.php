<?php

ini_set('memory_limit', '550M');

require 'vendor/autoload.php';

use Imagine\Image\Metadata\ExifMetadataReader;
use Imagine\Imagick\Imagine;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

$filesystem = new Filesystem();

$imagine = new Imagine();

$imagine->setMetadataReader(new ExifMetadataReader());

$src = __DIR__ . '/source-fotos';
$files = scandir($src);

$errors = [];

echo 'Source ' . count($files) . PHP_EOL;

foreach ($files as $file) {


    try {
        if (in_array($file, ['.', '..', '.DS_Store'])) {
            continue;
        }

        $explode = explode('.', $file);

        $fileName = array_shift($explode);
        $extension = end($explode);

        // video
        if(in_array(strtolower($extension), ['mp4', 'mv', 'move', 'mov'])) {

            preg_match('/^VID_([0-9]{8})/', $fileName, $matches);

            if(preg_match('/^VID_([0-9]{8})/', $fileName, $matches) && count($matches) === 2) {

                $timestamp = strtotime(end($matches));

                $year = date("Y", $timestamp);

                $yearAndMonth = date("Y-m", $timestamp);

                $newFile = __DIR__ . '/sorted-photos/video/' . $year . '/' . $yearAndMonth . '/' . str_replace(' ', '_', $file);

            } elseif(preg_match('/^([0-9]{8})/', $fileName, $matches) && count($matches) === 2) {

                $timestamp = strtotime(end($matches));

                $year = date("Y", $timestamp);

                $yearAndMonth = date("Y-m", $timestamp);

                $newFile = __DIR__ . '/sorted-photos/video/' . $year . '/' . $yearAndMonth . '/' . str_replace(' ', '_', $file);

            } elseif(preg_match('/([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))/', $fileName, $matches)) {

                $timestamp = strtotime(array_shift($matches));

                $date = date("Y-m-d_H-i-s", $timestamp);

                $year = date("Y", $timestamp);

                $yearAndMonth = date("Y-m", $timestamp);

                $newFile = __DIR__ . '/sorted-photos/video/' . $year . '/' . $yearAndMonth . '/' . str_replace(' ', '_', $file);

            } else {
                $newFile = __DIR__ . '/sorted-photos/video/' . str_replace(' ', '_', $file);
            }

        } else {
            // foto
            $image = $imagine->open($src . '/' . $file);

            $metadata = $image->metadata();

            list ($type, $mime) = explode('/', $metadata['file.MimeType']);

            $timestamp = strtotime($metadata['exif.DateTimeOriginal']);

            $date = date("Y-m-d_H-i-s", $timestamp);

            $year = date("Y", $timestamp);

            $yearAndMonth = date("Y-m", $timestamp);

            if($year === '1970' && preg_match('/^IMG_([0-9]{8})/', $fileName, $matches)) {

                $timestamp = strtotime(end($matches));

                $date = date("Y-m-d_H-i-s", $timestamp);

                $year = date("Y", $timestamp);

                $yearAndMonth = date("Y-m", $timestamp);
            }

            if($year === '1970' && preg_match('/^([0-9]{8})/', $fileName, $matches)) {

                $timestamp = strtotime(end($matches));

                $date = date("Y-m-d_H-i-s", $timestamp);

                $year = date("Y", $timestamp);

                $yearAndMonth = date("Y-m", $timestamp);
            }

            if($year === '1970' && preg_match('/([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))/', $fileName, $matches)) {

                $timestamp = strtotime(array_shift($matches));

                $date = date("Y-m-d_H-i-s", $timestamp);

                $year = date("Y", $timestamp);

                $yearAndMonth = date("Y-m", $timestamp);
            }

//            if ($yearAndMonth === '2012-01') {
//
//                $year = '2015';
//
//                $yearAndMonth = '2015-07';
//            }
//
//            if ($yearAndMonth === '2012-02') {
//
//                $year = '2015';
//
//                $yearAndMonth = '2015-08';
//            }

            $distDir = $year !== '1970'
                ? 'sorted-photos/' . $year . '/' . $yearAndMonth
                : 'sorted-photos/' . $year;

            $newFile = $year !== '1970'
                ? $distDir . '/' . $date . '_' . md5(json_encode($metadata->toArray())) . '.' . ($mime ? $mime :  $extension)
                : $distDir . '/' . str_replace(' ', '_', $file);


          //  $image->save($newFile);

        }

    } catch (Exception $e) {

        $errors[] = $file;
        $newFile =  __DIR__ . '/sorted-photos/unsorted/' . str_replace(' ', '_', $file);

        echo $e->getMessage() . PHP_EOL;

    }

    if($filesystem->exists($newFile)) {
        continue;
    }

    try {
        $filesystem->copy($src . '/' . $file, $newFile);

    }  catch (\Symfony\Component\Filesystem\Exception\IOException $e) {
        $errors[] = $file;
        echo $e->getMessage() . PHP_EOL;
    }

    var_dump($newFile);
}

$filesystem->dumpFile('log.json', json_encode($errors));


echo 'Source ' . count($files) . PHP_EOL;
echo 'Dist ' .  exec('find ./source-fotos -type f | wc -l');
echo "\nEND!!!\n";
