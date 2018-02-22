<?php

$scriptStartTime = time();

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

/** Initial sources */
$sources = [];
$filters = [];
$sources[] = '-i ' . $dstPath . $startTime . '.sound.wav';
$sources[] = '-loop 1 -i ' . $dstPath . 'layout.png';

/** Prepare presentation filters */
writeLn('...preparing presentation filters');

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
        count($sources) - 1
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
        count($sources) - 1
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
    count($sources) - 1
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
        count($sources) - 1
    );
}

/** Prepare webcam events */
writeLn('...preparing webcam events');
execute('php extractWebcamEvents.php --src=' . $srcPath . 'events.xml',
    $dstPath . 'webcam.events');

/** Prepare webcam filters */
writeLn('...preparing webcam filters');
$webcamEventsSource = extractCSV($dstPath . 'webcam.events', [0 => 'action', 1 => 'time', 2 => 'file']);
$meetingId = $events->findMeetingId();
if (null === $meetingId) {
    halt('Meeting ID not found');
}
$coords = $contents['VideoDock'];

$webcamEvents = [];
foreach($webcamEventsSource as $event) {
    if ('start' === $event['action']) {
        $webcamEvents[$event['file']] = [
            'start' => ($event['time'] - $startTime) / 1000,
            'end' => '100000',
            'source' => $srcPath . 'video' . DS . $meetingId . DS . $event['file'] . '.flv',
        ];
    } elseif ('stop' === $event['action']) {
        $webcamEvents[$event['file']]['end'] = ($event['time'] - $startTime) / 1000;
    }
}
foreach($webcamEvents as $key => $event) {
    $sources[] = '-itsoffset ' . $event['start'] . ' -i ' . $event['source'];
    $sourceResizedDimensions = getVideoResizedDimensions($event['source'], $coords['w'], $coords['h']);
    $sourceWidth = $sourceResizedDimensions['width'];
    $sourceHeight = (false === $sourceResizedDimensions['resize']) ? '-1' : $sourceResizedDimensions['height'];
    $coordX = round($coords['x'] + ($coords['w'] - $sourceResizedDimensions['width']) / 2);
    $coordY = round($coords['y'] + ($coords['h'] - $sourceResizedDimensions['height']) / 2);
    $filterScale = '[' . (count($sources) - 1) . ':v] scale=' .
        $sourceWidth . ':' . $sourceHeight . ' [' . (count($sources) - 1) . 's]';
    $filterOverflowTrim = '[' . (count($sources) - 1) . 's] ' .
        'trim=duration=' . ($event['end'] - $event['start']) . ' [' . (count($sources) - 1) . 't]';
    $filterOverflow = '[out]' . '[' . (count($sources) - 1) . 't]' .
        ' overlay=' . $coordX . ':' . $coordY . ':enable=\'between(t,' .
        $event['start'] . ',' . $event['end'] . ')\' [out]';
    $filters[] = $filterScale . ';' . $filterOverflowTrim . ';' . $filterOverflow;
}

/** Prepare deskshare events */
writeLn('...preparing deskshare events');
execute('php extractDeskshareEvents.php --src=' . $srcPath . 'events.xml',
    $dstPath . 'deskshare.events');

/** Prepare deskshare filters */
writeLn('...preparing deskshare filters');
$deskshareEventsSource = extractCSV($dstPath . 'deskshare.events', [0 => 'action', 1 => 'time', 2 => 'file']);

$eventsCount = 0;
$deskshareEvents = [];
foreach($deskshareEventsSource as $event) {
    if ('started' === $event['action']) {
        $eventsCount++;
        $deskshareEvents[$eventsCount] = [
            'start' => ($event['time'] - $startTime) / 1000,
            'end' => '100000',
            'source' => $srcPath . 'deskshare' . DS . basename($event['file']),
        ];
    } elseif ('stopped' === $event['action']) {
        $deskshareEvents[$eventsCount]['end'] = ($event['time'] - $startTime) / 1000;
    }
}
foreach($deskshareEvents as $key => $event) {
    $deskshareImage = $dstPath . 'deskshare' . DS . 'deskshare-' . $key . '.png';
    execute('php makeDeskshareLayout.php --width=' . $width . ' --height=' . $height .
        ' --src=' . $event['source'] . ' --dst=' . $deskshareImage .
        ' --pad=10', $dstPath . 'deskshare.coords');
    $coords = extractCSV($dstPath . 'deskshare.coords', [1 => 'lx', 2 => 'ly', 5 => 'x', 6 => 'y', 7 => 'w', 8 => 'h', 9 => 'resize'])[0];

    $sources[] = '-i ' . $deskshareImage;
    $filterLayout = '[out]' . '[' . (count($sources) - 1) . ':v]' .
        ' overlay=' . $coords['lx'] . ':' . $coords['ly'] . ':enable=\'between(t,' .
        $event['start'] . ',' . $event['end'] . ')\' [out]';
    $sources[] = '-itsoffset ' . $event['start'] . ' -i ' . $event['source'];
    if ('1' === $coords['resize']) {
        $filterScale = '[' . (count($sources) - 1) . ':v] scale=' .
            $coords['w'] . ':-1 [' . (count($sources) - 1) . 's]';
    }
    $filterOverflowTrim = '[' . (count($sources) - 1) . (isset($filterScale) ? 's' : 'v') .
        '] trim=duration=' . ($event['end'] - $event['start']) . ' [' . (count($sources) - 1) . 't]';
    $filterOverflow = '[out]' . '[' . (count($sources) - 1) . 't]' .
        ' overlay=' . $coords['x'] . ':' . $coords['y'] . ':enable=\'between(t,' .
        $event['start'] . ',' . $event['end'] . ')\' [out]';
    $filters[] = $filterLayout . ';' . (isset($filterScale) ? ($filterScale . ';') : '') .
        $filterOverflowTrim . ';' . $filterOverflow;
}

/** Combine video */
writeLn('...combining video');

exec('ffmpeg -v quiet -stats -y ' . implode(' ', $sources) .
    ' -filter_complex "' . implode(';', $filters) .
    '" -map "[out]" -map 0:0 -c:v libx264 -preset ultrafast -pix_fmt yuv420p -c:a copy ' .
    '-shortest ' . $dstPath . 'video.avi');

$workTime = time() - $scriptStartTime;
$hours = floor($workTime / 3600);
$minutes = floor(($workTime % 3600) / 60);
$seconds = $workTime % 60;
writeLn('Total working time: ' . $hours . 'h ' . $minutes . 'm ' . $seconds . 's ');
