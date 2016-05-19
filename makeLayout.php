<?php
/**
 * @use php makeLayout.php --width=1280 --height=720 --dst=test.png --pad=5 --fill-content-zone > content.coords
 */
require __DIR__ . '/autoload.php';

define('DS', DIRECTORY_SEPARATOR);
define('DESKSHARE_X', 0.1);
define('DESKSHARE_Y', 0.1);
define('DESKSHARE_W', 0.8);
define('DESKSHARE_H', 0.8);

$options = getopt('', ['width:', 'height:', 'dst:', 'pad:', 'fill-content-zone']);
$width = $options['width'] ?? 1280;
$height  = $options['height'] ?? 720;
$pad = $options['pad'] ?? 2;
$fillContent = isset($options['fill-content-zone']);
$dstFileName = isset($options['dst']) ? (realpath(dirname($options['dst'])) . DS . basename($options['dst'])) : 'test.png';

$css = new \ProfIT\Bbb\Layout\StyleSheet(__DIR__ . '/resources/style/css/BBBDefault.css');
$layout = new \ProfIT\Bbb\Layout(__DIR__ . '/resources/layout.xml', 'defaultlayout', $css, $pad);

$image = new \ProfIT\Bbb\Layout\Image($width, $height);
$image->applyCSS($css);
$image->generateLayout($layout, $dstFileName, $fillContent);

$layoutParams = [
    'name' => 'Layout',
    'x' => 0,
    'y' => 0,
    'w' => $width,
    'h' => $height,
];
echo implode(',', $layoutParams) . PHP_EOL;
foreach ($image->getWindows() as $window) {
    /** @var \ProfIT\Bbb\Layout\Window $window */
    echo implode(',', $window->getContentCoordinates()) . PHP_EOL;
}
$deskshareParams = [
    'name' => 'Deskshare',
    'x' => (int) round(DESKSHARE_X * $width),
    'y' => (int) round(DESKSHARE_Y * $height),
    'w' => (int) round(DESKSHARE_W * $width),
    'h' => (int) round(DESKSHARE_H * $height),
    'pad' => $pad,
];
echo implode(',', $deskshareParams) . PHP_EOL;
