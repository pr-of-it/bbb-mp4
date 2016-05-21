<?php
/**
 * @use php extractPresentationSlides.php --source=./source FilePath/*.pdf --size=1080x830 --save=./imageFilePath
 */
require __DIR__ . '/autoload.php';

$options = getopt('', ['source:', 'size:', 'save:']);
$sourceFilePath = realpath($options['source']);
$imageSize = $options['size'];
$imageFilePath = realpath($options['save']);

if (file_exists($sourceFilePath)) {
    exec('convert -density 300 ' . $sourceFilePath . ' -resize ' . $imageSize . ' ' . $imageFilePath . '/slide.png');
} else {
    echo 'File does not exist';
    exit(0);
}