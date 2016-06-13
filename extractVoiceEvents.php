<?php
/**
 * @use php extractVoiceEvents.php --src=events.xml > voice.events
 */
require __DIR__ . '/autoload.php';
require __DIR__ . '/functions.php';

$options = getopt('', ['src:']);
$srcFileName = realpath($options['src']);

if (!is_readable($srcFileName)) {
    halt('File does not exist or is not readable');
}

$events = new \ProfIT\Bbb\EventsFile($srcFileName);

try {
    $fragments = $events->extractFragments(
        '~<event.+module="VOICE"\s+eventname="\w+RecordingEvent">~',
        '~</event>~'
    );

    foreach ($fragments as $fragment) {
        $eventParams = [];

        preg_match('~<event\s+timestamp="(\d+)".+eventname="(\w+)RecordingEvent">~', $fragment, $m);
        $eventParams[0] = lcfirst($m[2]);
        $eventParams[1] = $m[1];
        preg_match('~<filename>(.+)</filename>~', $fragment, $m);
        $eventParams[2] = $m[1];
        
        fputcsv(STDOUT, $eventParams);
    }
} catch (\ProfIT\Bbb\Exception $e) {
    halt($e->getMessage() . PHP_EOL);
}
