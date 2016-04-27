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
        '~<event.+module="VOICE".+>~U',
        '~</event>~'
    );

    foreach ($fragments as $fragment) {
        echo $fragment . PHP_EOL;
    }
} catch (\ProfIT\Bbb\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}
