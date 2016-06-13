<?php
/**
 * @use php makeDeskshareLayout.php --width=1280 --height=720 --dst=deskshare.png --pad=10 --fill-content-zone > deskshare.coords
 */

require __DIR__ . '/autoload.php';

define('DS', DIRECTORY_SEPARATOR);

$options = getopt('', ['width:', 'height:', 'dst:', 'pad:', 'fill-content-zone']);
$width = $options['width'] ?? 1280;
$height  = $options['height'] ?? 720;
$pad = $options['pad'] ?? 2;
$fillContent = isset($options['fill-content-zone']);
$dstFileName = isset($options['dst']) ? (realpath(dirname($options['dst'])) . DS . basename($options['dst'])) : 'test.png';

$css = new \ProfIT\Bbb\Layout\StyleSheet(__DIR__ . '/resources/style/css/BBBDefault.css');
$layout = new \ProfIT\Bbb\Layout(__DIR__ . '/resources/layout.xml', 'defaultlayout', $css);
$layout->setDimensions($width, $height, $pad);
$layout->addCustomWindow([
    'name' => 'Deskshare',
    'x' => 0.1,
    'y' => 0.1,
    'w' => 0.8,
    'h' => 0.8,
]);
$layout->setMarkedWindows(['Deskshare']);
$layout->generatePng($dstFileName, $fillContent, true);

foreach ($layout->getWindows() as $window) {
    /** @var \ProfIT\Bbb\Layout\Window $window */
    fputcsv(STDOUT, $window->getCoordinates());
}
