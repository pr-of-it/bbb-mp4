<?php
/**
 * @use php makeSound.php --src=voice.events --dst=sound.wav
 */
require __DIR__ . '/autoload.php';

$options = getopt('', ['src:', 'dst:']);
$srcFileName = realpath($options['src']);
$dstFileName = $options['dst'];

$events = new \ProfIT\Bbb\EventsFile($srcFileName);

$fileNames = [];
try {
    $fragments = $events->extractFragments(
        '~<event.+eventname="StartRecordingEvent">~',
        '~</event>~'
    );

    foreach ($fragments as $fragment) {
        if (preg_match('~<filename>(.*)</filename>~', $fragment, $m)) {
            $fileNames[] = $m[1];
        }
    }
} catch (\ProfIT\Bbb\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}

$totalSize = 0;

foreach ($fileNames as $fileName) {

    $wav = new \ProfIT\Bbb\WavFile($fileName);

    $totalSize += $wav->headers['chunkSize'];
    $wav->headers['chunkSize'] = $totalSize;

    $wav->exportHeaders($dstFileName);
    $wav->exportData($dstFileName);
}
