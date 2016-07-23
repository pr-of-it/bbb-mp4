<?php
/**
 * @use php makeDeskshareLayout.php --width=1280 --height=720 --src=video.flv --dst=deskshare.png --pad=10 > deskshare.coords
 */

require __DIR__ . '/autoload.php';
require __DIR__ . '/imageFunctions.php';

define('DS', DIRECTORY_SEPARATOR);

$options = getopt('', ['width:', 'height:', 'src:', 'dst:', 'pad:']);
$width = $options['width'] ?? 1280;
$height  = $options['height'] ?? 720;
$pad = $options['pad'] ?? 2;
$dstFileName = $options['dst'];
$srcFileName = realpath($options['src']);

if (!is_readable($srcFileName)) {
    halt('Video file does not exist or is not readable');
}
if (!file_exists(dirname($dstFileName))) {
    mkdir(dirname($dstFileName));
}

$css = new \ProfIT\Bbb\Layout\StyleSheet(__DIR__ . '/resources/style/css/BBBDefault.css');
$titleHeight = (int)$css->rules['.videoViewStyleNoFocus']['headerHeight'];
$contentWidth = $width - 2 * $pad;
$contentHeight = $height - $titleHeight - 3 * $pad;

$videoResizedDimensions = getVideoResizedDimensions($srcFileName, $contentWidth, $contentHeight);
$videoWidth = $videoResizedDimensions['width'];
$videoHeight = $videoResizedDimensions['height'];
$videoResized = $videoResizedDimensions['resize'];

$layoutParams = [
    'w' => $videoWidth + 2 * $pad,
    'h' => $videoHeight + $titleHeight + 3 * $pad,
    'x' => round(($width - ($videoWidth + 2 * $pad)) / 2),
    'y' => round(($height - ($videoHeight + $titleHeight + 3 * $pad)) / 2),
    'resize' => $videoResized,
];

$layout = new \ProfIT\Bbb\Layout(__DIR__ . '/resources/layout.xml', 'defaultlayout', $css);
$layout->setDimensions($layoutParams['w'], $layoutParams['h'], $pad);
$layout->addCustomWindow([
    'name' => 'Deskshare',
    'x' => 0,
    'y' => 0,
    'w' => 1,
    'h' => 1,
]);
$layout->setMarkedWindows(['Deskshare']);
$layout->generatePng($dstFileName, false, true);

$windows = $layout->getWindows();
$layoutWindow = array_pop($windows);
$layoutCoordinates = $layoutWindow->getCoordinates();
$layoutCoordinates[1] = $layoutParams['x'];
$layoutCoordinates[2] = $layoutParams['y'];
$layoutCoordinates[5] += $layoutParams['x'];
$layoutCoordinates[6] += $layoutParams['y'];
$layoutCoordinates[9] = $layoutParams['resize'];

fputcsv(STDOUT, $layoutCoordinates);
