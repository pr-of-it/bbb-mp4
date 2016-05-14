<?php
/**
 * @use php makeLayout.php --width=1280 --height=720 --dst=test.png > contents.coords
 */
require __DIR__ . '/autoload.php';

define('DS', DIRECTORY_SEPARATOR);

$options = getopt('', ['width:', 'height:', 'dst:']);
$width = $options['width'] ?? 1280;
$height  = $options['height'] ?? 720;
$dstFileName = isset($options['dst']) ? (realpath(dirname($options['dst'])) . DS . basename($options['dst'])) : 'test.png';

$css = new \ProfIT\Bbb\Layout\StyleSheet(__DIR__ . '/resources/style/css/BBBDefault.css');
$layout = new \ProfIT\Bbb\Layout(__DIR__ . '/resources/layout.xml', 'defaultlayout', $css);

$image = new \ProfIT\Bbb\Layout\Image($css, ['w' => $width, 'h' => $height]);
$contents = $image->loadLayout($layout);
$image->generatePng($dstFileName);

foreach ($contents as $content) {
    echo implode(',', $content) . PHP_EOL;
}