<?php

$startTime = time();

/**
 * @use php process.php --src=./source --width=1280 --height=720 --dst=./results/
 */
require __DIR__ . '/autoload.php';
require __DIR__ . '/functions.php';

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
writeLn('...preparing layout');
execute('php makeLayout.php --width=' . $width . ' --height=' . $height .
    ' --dst=' . $dstPath . 'layout.png --pad=10 --fill-content-zone',
    $dstPath . 'content.coords');

/** Prepare voice events */
writeLn('...preparing voice events');
execute('php extractVoiceEvents.php --src=' . $srcPath . 'events.xml',
    $dstPath . 'voice.events');

/** Prepare sound */
writeLn('...preparing sound');
execute('php makeSound.php --src=' . $dstPath . 'voice.events' .
    ' --src-dir=' . $srcPath . DS . 'audio --dst=' .
    $dstPath . 'sound.wav');
foreach (scandir($dstPath) as $file) {
    if (preg_match('~(\d+).sound.wav~', $file, $m)) {
        $soundStart = $m[1];
        break;
    }
}
if (empty($soundStart)) {
    halt('Sound preparation fault. Sound file not found.');
}

/** Prepare presentation events */
writeLn('...preparing presentation events');
execute('php extractPresentationEvents.php --path=' . $srcPath . 'presentation ' .
    '--src=' . $srcPath . 'events.xml --dst=' . $dstPath . 'events.wp.xml',
    $dstPath . 'presentation.events');

/** Prepare presentation slides */
writeLn('...preparing presentation slides');
$presentations = extractCSV($dstPath . 'presentation.events', ['time', 'file', 'slide']);
$presentationFiles = array_unique(array_column($presentations, 'file'));
if (!file_exists($dstPath . 'slides')) {
    mkdir($dstPath . 'slides');
}
$contents = extractCSV($dstPath . 'content.coords', [0 => 'window', 5 => 'x', 6 => 'y', 7 => 'w', 8 => 'h']);
$contents = array_column($contents, null, 'window');
$coords = $contents['PresentationWindow'];
foreach ($presentationFiles as $pdf) {
    execute('php extractPresentationSlides.php --source=' . $pdf .
        ' --width=' . $coords['w'] . ' --height=' . $coords['h'] .
        ' --save=' . $dstPath . 'slides' . DS . basename($pdf, '.pdf'));
}

/** Combine video with presentation */
writeLn('...combining video with presentation');

$sources = [];
$filters = [];
foreach ($presentations as $key => $p) {
    $slide = $dstPath . 'slides' . DS . basename($p['file'], '.pdf') . DS . 'slide-' . $p['slide'] . '.png';
    $slideSize = getimagesize($slide);
    $slideOffsetY = round($coords['y'] + (($coords['h'] - $slideSize[1]) / 2));
    $slideStartTime = ($p['time'] - $soundStart) / 1000;
    $slideEndTime =
        isset($presentations[$key + 1]) ? (($presentations[$key + 1]['time'] - $soundStart) / 1000) : '100000';
    $sources[] = '-i ' . $slide;
    $filters[] = (0 === $key ? '[1:v]' : '[out]') . '[' . ($key + 2) . ':v]' .
        ' overlay=' . $coords['x'] . ':' . $slideOffsetY . ':enable=\'between(t,' .
        $slideStartTime . ',' . $slideEndTime . ')\' [out]';
}

exec('ffmpeg -loglevel quiet -stats -y -i ' . $dstPath . $soundStart . '.sound.wav -loop 1 -i ' .
    $dstPath . 'layout.png ' . implode(' ', $sources) . ' -filter_complex "' . implode(';', $filters) .
    '" -map "[out]" -map 0:0 -c:v libx264 -preset ultrafast -pix_fmt yuv420p -c:a copy ' .
    '-shortest ' . $dstPath . 'video_presentation.avi');

$workTime = time() - $startTime;
$hours = floor($workTime / 3600);
$minutes = floor(($workTime % 3600) / 60);
$seconds = $workTime % 60;
writeLn('Total working time: ' . $hours . 'h ' . $minutes . 'm ' . $seconds . 's ');
