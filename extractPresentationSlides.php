<?php
/**
 * @use php extractPresentationSlides.php --source=./sourceFilePath/presentation.pdf --width=1280 --height=720 --save=./imageFilePath
 */
require __DIR__ . '/autoload.php';
require __DIR__ . '/functions.php';

$options = getopt('', ['source:', 'width:', 'height:', 'save:']);
$sourceFilePath = realpath($options['source']);
$width = $options['width'] ?? 1280;
$height = $options['height'] ?? 720;
$imageFilePath = $options['save'];

if (!file_exists($sourceFilePath)) {
    halt('File does not exist');
}

if (!file_exists(__DIR__ . $imageFilePath)) {
    mkdir(__DIR__ . $imageFilePath);
}

$directory = realpath($imageFilePath);
$command = 'convert -density 150 -scene 1 ' . $sourceFilePath . ' -resize ' . $width . 'x' . $height . ' ' . $directory . '/slide.png';
execute($command);