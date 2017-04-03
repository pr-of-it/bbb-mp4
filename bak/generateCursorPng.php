<?php

/**
 * @use php generateCursorPng.php --src=./cursor.events --dst=./cursor/ --width=1280 --height=720 --diameter=10
 */

$options = getopt('', ['src:', 'dst:', 'width:', 'height:', 'diameter:']);
$dstDirName = $options['dst'];
if (!is_readable($dstDirName)) {
    mkdir($dstDirName, 0777);
}
$dstDirName = realpath($dstDirName);

$w = $options['width'];
$h = $options['height'];
$d = $options['diameter'];

$s = fopen($options['src'], 'r');
while (false !== $line = fgetcsv($s)) {

    $img = imagecreatetruecolor($w, $h);
    imagealphablending($img, false);
    $transparency = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparency);
    imagesavealpha($img, true);

    $color = imagecolorallocatealpha($img, 0xFF, 0x00, 0x00, 75);
    $x = $line[1] * $w;
    $y = $line[2] * $h;
    imagefilledellipse($img, $x, $y, $d, $d, $color);

    imagepng($img, $dstDirName . '/' . $line[0] . '.png');
}