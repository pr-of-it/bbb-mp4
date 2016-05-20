<?php
/**
 * @use php extractPresentationSlides.php --src= --size= --save=./
 */
require __DIR__ . '/autoload.php';

$options = getopt('', ['src:', 'size:', 'save:']);
$srcSource = realpath($options['src']);
$sizeImage = realpath($options['size']);
$srcImageSave = realpath($options['save']);

$src = fopen($srcSource, 'r');
if (false === $src) {
    echo 'Error while opening file: ' . $srcFileName . PHP_EOL;
    exit(0);
}

while (false !== $src) {
    
}