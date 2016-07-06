<?php

$startTime = time();

/**
 * @use php process.php --src=./source --width=1280 --height=720 --dst=./results/
 */
require __DIR__ . '/autoload.php';
require __DIR__ . '/functions.php';
require __DIR__ . '/imageFunctions.php';

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

$events = new \ProfIT\Bbb\EventsFile($srcPath . 'events.xml');

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
        $startTime = $m[1];
        break;
    }
}
if (empty($startTime)) {
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

/** Prepare presentation filters */
writeLn('...preparing presentation filters');

$sources = [];
$filters = [];
foreach ($presentations as $key => $p) {
    $slide = $dstPath . 'slides' . DS . basename($p['file'], '.pdf') . DS . 'slide-' . $p['slide'] . '.png';
    $slideSize = getimagesize($slide);
    $sources[] = '-i ' . $slide;
    addImageToFilters(
        $filters,
        ($p['time'] - $startTime) / 1000,
        isset($presentations[$key + 1]) ? (($presentations[$key + 1]['time'] - $startTime) / 1000) : '100000',
        $coords['x'],
        round($coords['y'] + (($coords['h'] - $slideSize[1]) / 2)),
        $key + 2
    );
}

/** Prepare user events */
writeLn('...preparing user events');
execute('php extractUserEvents.php --src=' . $srcPath . 'events.xml',
    $dstPath . 'user.events');
$userEvents = extractCSV($dstPath . 'user.events', [0 => 'action', 1 => 'time', 2 => 'id', 3 => 'name']);

/** Prepare user-list images and filters */
writeLn('...preparing user-list images');
$userList = [];
$userImagesCount = 0;
$coords = $contents['UsersWindow'];
foreach ($userEvents as $key => $event) {
    if ('join' === $event['action']) {
        $userList[$event['id']] = $event['name'];
    } elseif ('left' === $event['action']) {
        unset($userList[$event['id']]);
    } else {
        continue;
    }
    $userImagesCount++;
    $image = $dstPath . 'users' . DS . 'list.' . $event['time'] . '.png';
    generateListImage($image, $coords, $userList);
    $sources[] = '-i ' . $image;
    addImageToFilters(
        $filters,
        ($event['time'] - $startTime) / 1000,
        isset($userEvents[$key + 1]) ? (($userEvents[$key + 1]['time'] - $startTime) / 1000) : '100000',
        $coords['x'],
        $coords['y'],
        $key + 2 + count($presentations)
    );
}

/** Prepare chat events */
writeLn('...preparing chat events');
execute('php extractChatEvents.php --src=' . $srcPath . 'events.xml --dst=' . $dstPath . 'events.wc.xml',
    $dstPath . 'chat.events');
$chatEvents = extractCSV($dstPath . 'chat.events', [0 => 'time', 1 => 'user', 2 => 'message']);

/** Prepare chat-list images and filters */
writeLn('...preparing chat-list images');
$chatList = [];
$coords = $contents['ChatWindow'];
/** Chat caption from start */
$image = $dstPath . 'chat' . DS . 'list.' . $startTime . '.png';
generateChatListImage($image, $coords, $chatList, $events);
$sources[] = '-i ' . $image;
addImageToFilters(
    $filters,
    0,
    isset($chatEvents[0]) ? (($chatEvents[0]['time'] - $startTime) / 1000) : '100000',
    $coords['x'],
    $coords['y'],
    2 + count($presentations) + $userImagesCount
);
foreach ($chatEvents as $key => $event) {
    $chatList[] = $event;
    $image = $dstPath . 'chat' . DS . 'list.' . $event['time'] . '.png';
    generateChatListImage($image, $coords, $chatList, $events);
    $sources[] = '-i ' . $image;
    addImageToFilters(
        $filters,
        ($event['time'] - $startTime) / 1000,
        isset($chatEvents[$key + 1]) ? (($chatEvents[$key + 1]['time'] - $startTime) / 1000) : '100000',
        $coords['x'],
        $coords['y'],
        $key + 3 + count($presentations) + $userImagesCount
    );
}

/** Combine video */
writeLn('...combining video');

exec('ffmpeg -loglevel quiet -stats -y -i ' . $dstPath . $startTime . '.sound.wav -loop 1 -i ' .
    $dstPath . 'layout.png ' . implode(' ', $sources) . ' -filter_complex "' . implode(';', $filters) .
    '" -map "[out]" -map 0:0 -c:v libx264 -preset ultrafast -pix_fmt yuv420p -c:a copy ' .
    '-shortest ' . $dstPath . 'video_presentation.avi');

$workTime = time() - $startTime;
$hours = floor($workTime / 3600);
$minutes = floor(($workTime % 3600) / 60);
$seconds = $workTime % 60;
writeLn('Total working time: ' . $hours . 'h ' . $minutes . 'm ' . $seconds . 's ');
