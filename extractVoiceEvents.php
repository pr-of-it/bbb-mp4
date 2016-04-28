<?php
/**
 * @use php extractVoiceEvents.php --src=events.xml > voice.events
 */
require __DIR__ . '/autoload.php';

$options = getopt('', ['src:']);
$srcFileName = realpath($options['src']);

$events = new \ProfIT\Bbb\EventsFile($srcFileName);

try {
    $fragments = $events->extractFragments(
        '~<event.+module="VOICE"\s+eventname="\w+RecordingEvent">~',
        '~</event>~'
    );

    foreach ($fragments as $fragment) {
        $eventParams = [];

        if (preg_match('~<event\s+timestamp="(\d+)".+eventname="(\w+)RecordingEvent">~', $fragment, $m)) {
            $eventParams[0] = $m[2];
            $eventParams[1] = $m[1];
        }
        if (preg_match('~<filename>(.+)</filename>~', $fragment, $m)) {
            $eventParams[2] = $m[1];
        }

        echo implode(',', $eventParams) . PHP_EOL;
    }
} catch (\ProfIT\Bbb\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}
