<?php
/**
 * @use php makeSound.php --src=voice.events --src-dir=./resources/audio --dst=sound.wav
 */
require __DIR__ . '/autoload.php';

define('SOX_PATH', 'sox');

$options = getopt('', ['src:', 'src-dir:', 'dst:']);
$srcFileName = realpath($options['src']);
$srcDirName  = realpath($options['src-dir']);
$dstFileName = realpath(dirname($options['dst'])) . DIRECTORY_SEPARATOR . basename($options['dst']);

$src = fopen($srcFileName, 'r');
if (false === $src) {
    echo 'Ошибка открытия файла: ' . $srcFileName;
    exit(0);
}

$fragments = [];

while ($csv = fgetcsv($src, 1024)) {
    var_dump($csv);
    if ('Start' === $csv[0]) {

        $fragmentSource = empty($srcDirName) ? $csv[2] : $srcDirName . DIRECTORY_SEPARATOR . basename($csv[2]);

        if (isset($firstFragmentStart)) {
            $fragmentDelay = ((int)$csv[1] - $firstFragmentStart) / 1000;
            $delayPart = ' pad ' . $fragmentDelay . ' 0';
        } else {
            $firstFragmentStart = (int)$csv[1];
            $firstFragmentSource = $fragmentSource;
            $delayPart = '';
        }

        $fragments[] = ' -v 1 "|' . SOX_PATH . ' ' . $fragmentSource . ' -p' . $delayPart . '"';
    }
}

fclose($src);

if (isset($firstFragmentSource)) {
    $execString = SOX_PATH . ' -m' . implode('', $fragments) . ' -b 16 ' . $dstFileName;
    exec($execString);
    echo 'Сборка звука завершена' .PHP_EOL;
} else {
    echo 'Голосовые фрагменты не найдены' . PHP_EOL;
}
