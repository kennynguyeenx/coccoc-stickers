#!/usr/bin/env php
<?php
use Kennynguyeenx\ImageService\ImageService;

require __DIR__ . '/../vendor/autoload.php';

if (!isset($argv[4])) {
    exit('Missing output format' . PHP_EOL);
}

if (!isset($argv[3])) {
    exit('Missing output image path' . PHP_EOL);
}

if (!isset($argv[2])) {
    exit('Missing options' . PHP_EOL);
}

if (!preg_match("/^(\d+)x(\d+)\_(.*)$/", $argv[2], $matches)) {
    exit('Invalid options' . PHP_EOL);
}

if (!isset($argv[1])) {
    exit('Missing source image path' . PHP_EOL);
}

list($size, $backgroundColor) = explode('_', $argv[2]);
list($width, $height) = explode('x', $size);
$options = [
    'width' => $width,
    'height' => $height,
    'background' => $backgroundColor
];

try {
    $imageService = new ImageService();
    $imageService->manipulateImage($argv[1], $options, $argv[3], $argv[4]);
} catch (Exception $exception) {
    exit($exception->getMessage() . PHP_EOL);
}

exit('Done' . PHP_EOL);