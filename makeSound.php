<?php
/**
 * @use php makeSound.php --src=voice.events --dst=sound.wav
 */
require __DIR__ . '/autoload.php';

$options = getopt('', ['src:', 'dst:']);
$srcFileName = realpath($options['src']);
$dstFileName = $options['dst'];

$events = new \ProfIT\Bbb\EventsFile($srcFileName);

$sounds = [];
try {
    $fragments = $events->extractFragments(
        '~<event.+eventname=".+RecordingEvent">~',
        '~</event>~'
    );

    $index = 0;
    $capture = false;
    foreach ($fragments as $fragment) {
        if (preg_match('~<event\s+timestamp="(\d+)".+eventname="StartRecordingEvent">~', $fragment, $m)) {
            $sounds[$index]['start'] = (int)$m[1];
            $capture = true;
        }
        if ($capture && preg_match('~<filename>(.*)</filename>~', $fragment, $m)) {
            $sounds[$index]['fileName'] = $m[1];
        }
        if ($capture && preg_match('~<event\s+timestamp="(\d+)".+eventname="StopRecordingEvent">~', $fragment, $m)) {
            $sounds[$index]['stop'] = (int)$m[1];
            $capture = false;
            $index++;
        }
    }

    $headerOffset = \ProfIT\Bbb\WavFile::HEADER_OFFSET;
    $dataOffset = \ProfIT\Bbb\WavFile::DATA_OFFSET;
    $dataChunkSize = 0;

    foreach ($sounds as $index => $sound) {

        $wav = new \ProfIT\Bbb\WavFile($sound['fileName']);
        if (0 !== $index) {
            $pauseTime = $sound['start'] - $sounds[$index-1]['stop'];

            $pauseBytes = $wav->calculateBytesByTime($pauseTime);
            $dataChunkSize += $pauseBytes;
            $wav->exportPause($dstFileName, $pauseBytes);
        }

        $dataChunkSize += $wav->headers['dataChunkSize'];
        $wav->headers['dataChunkSize'] = $dataChunkSize;
        $wav->headers['headerFileSize'] = $dataChunkSize + $dataOffset - $headerOffset;

        $wav->exportHeaders($dstFileName);
        $wav->exportData($dstFileName);
    }
} catch (\ProfIT\Bbb\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}
