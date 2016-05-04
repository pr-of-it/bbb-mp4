<?php
/**
 * @use php makeLayout.php --width=1280 --height=720 --dst=test.png
 */
require __DIR__ . '/autoload.php';

$options = getopt('', ['width:', 'height:', 'dst:']);
$width = $options['width'] ?? 1280;
$height  = $options['height'] ?? 720;
$dstFileName = isset($options['dst']) ? (realpath(dirname($options['dst'])) . DS . basename($options['dst'])) : 'test.png';

$layout = new \ProfIT\Bbb\Layout(__DIR__ . '/resources/layout.xml', 'defaultlayout');
$layout->setStyleSheet(__DIR__ . '/resources/style/css/BBBDefault.css');

$image = new \ProfIT\Bbb\Layout\Image([
    'w' => $width,
    'h' => $height
]);
$image->loadLayout($layout);
$image->generatePng($dstFileName);
