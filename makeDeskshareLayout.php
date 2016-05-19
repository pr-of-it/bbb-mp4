<?php
/**
 * @use php makeDeskshareLayout.php --src=content.coords --dst=deskshare.png
 */
require __DIR__ . '/autoload.php';

define('DS', DIRECTORY_SEPARATOR);

$options = getopt('', ['src:', 'dst:']);
$srcFileName = realpath($options['src']);
$dstFileName = isset($options['dst']) ? (realpath(dirname($options['dst'])) . DS . basename($options['dst'])) : 'test.png';

$css = new \ProfIT\Bbb\Layout\StyleSheet(__DIR__ . '/resources/style/css/BBBDefault.css');

$src = fopen($srcFileName, 'r');
$layoutParams = [];
$deskshareParams = [];
while ($csv = fgetcsv($src, 1024)) {
    if ('Deskshare' === $csv[0]) {
        $deskshareParams = $csv;
    }
    if ('Layout' === $csv[0]) {
        $layoutParams = $csv;
    }
}
fclose($src);

$image = new \ProfIT\Bbb\Layout\Image($layoutParams[3], $layoutParams[4]);
$image->applyCSS($css);
$image->generateWindowPng($deskshareParams, $dstFileName);
