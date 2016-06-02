<?php

$startTime = time();

/**
 * @use php process.php --src=./source --width=1280 --height=720 --dst=./results/
 */
require __DIR__ . '/functions.php';

define('DS', DIRECTORY_SEPARATOR);

$options = getopt('', ['src:', 'width:', 'height:', 'dst:']);
$srcPath = realpath($options['src']) . DS;
$width = $options['width'] ?? 1280;
$height = $options['height'] ?? 720;
$dstPath = $options['dst'];

if (!is_readable($srcPath)) {
    halt('Source directory does not exist or is not readable');
}
if (!is_readable($dstPath)) {
    mkdir($dstPath, 0777);
}
$dstPath = realpath($dstPath) . DS;

/** Prepare layout and content coordinates */
echo '...preparing layout' . PHP_EOL;
execute('php makeLayout.php --width=' . $width . ' --height=' . $height .
    ' --dst=' . $dstPath . 'layout.png --pad=10 --fill-content-zone',
    $dstPath . 'content.coords');

/** Prepare voice events */
echo '...preparing voice events' . PHP_EOL;
execute('php extractVoiceEvents.php --src=' . $srcPath . 'events.xml',
    $dstPath . 'voice.events');

/** Prepare sound */
echo '...preparing sound' . PHP_EOL;
execute('php makeSound.php --src=' . $dstPath . 'voice.events' .
    ' --src-dir=' .$srcPath . DS . 'audio --dst=' .
    $dstPath . 'sound.wav');
foreach(scandir($dstPath) as $file) {
    if (preg_match('~(\d+).sound.wav~', $file, $m)) {
        $soundStart = $m[1];
        break;
    }
}
if (empty($soundStart)) {
    halt('Sound preparation fault. Sound file not found.');
}

/** Combine layout with sound */
echo '...combining layout with sound' . PHP_EOL;
execute('ffmpeg -loglevel quiet -stats -y -i ' . $dstPath . $soundStart . '.sound.wav -loop 1 -i ' .
    $dstPath . 'layout.png -c:v flv -c:a copy -shortest ' . $dstPath . 'video.flv');

/** Prepare presentation events */
echo '...preparing presentation events' . PHP_EOL;
execute('php extractPresentationEvents.php --path=' . $srcPath . 'presentation ' .
    '--src=' . $srcPath . 'events.xml --dst=' . $dstPath . 'events.wp.xml',
    $dstPath . 'presentation.events');

/** Prepare presentation slides */
echo '...preparing presentation slides' . PHP_EOL;
$presentations = extractCSV($dstPath . 'presentation.events', ['time', 'file', 'slide']);
$presentationFiles = array_unique(array_column($presentations, 'file'));
if (!file_exists($dstPath . 'slides')) {
    mkdir($dstPath . 'slides');
}
$contents = extractCSV($dstPath . 'content.coords', [0 => 'window', 5 => 'x', 6 => 'y', 7 => 'w', 8 => 'h']);
$contents = array_column($contents, null, 'window');
$coords = $contents['PresentationWindow'];
foreach ($presentationFiles as $pdf) {
    execute('php extractPresentationSlides.php --source='. $pdf .
        ' --width=' . $coords['w'] . ' --height=' . $coords['h'] .
        ' --save=' . $dstPath . 'slides' . DS . basename($pdf, '.pdf'));
}

/** Combine video with presentation */
echo '...combining video with presentation' . PHP_EOL;

$sources = [];
$filters = [];
foreach ($presentations as $key => $p) {
    $slide = $dstPath . 'slides' . DS . basename($p['file'], '.pdf') . DS . 'slide-' . $p['slide'] . '.png';
    $offset = ($p['time'] - $presentations[0]['time']) / 1000;
    $sources[] = '-i ' . $slide;
    $filters[] = (0 === $key ? '[0:v]' : '[out]') . '[' . ($key + 1) . ':v]' .
        ' overlay=' . $coords['x'] . ':' . $coords['y'] . ':enable=\'between(t,' . $offset . ',7200)\' [out]';
}

execute('ffmpeg -loglevel quiet -stats -y -i ' . $dstPath . 'video.flv ' .
    implode(' ', $sources) . ' -filter_complex "' . implode(';', $filters) .
    '" -map "[out]" -map a:0 -c:v flv -c:a copy ' . $dstPath . 'video_presentation.flv');

$workTime = time() - $startTime;
$hours = floor($workTime / 3600);
$minutes = floor(($workTime % 3600) / 60);
$seconds = $workTime % 60;
echo 'Total working time: ' . $hours . 'h ' . $minutes . 'm ' . $seconds . 's ' . PHP_EOL;
