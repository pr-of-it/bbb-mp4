<?php
/**
 * @use php extractPresentationEvents.php --src=events.xml --dst=events.new.xml > presentation.events
 */
require __DIR__ . '/autoload.php';

$options = getopt('', ['src:', 'dst:']);
$srcFileName = realpath($options['src']);
$dstFileName = $options['dst'];

$events = new \ProfIT\Bbb\EventsFile($srcFileName);

try {
    $fragments = $events->extractFragments(
        '~<event.+eventname="GotoSlideEvent">~',
        '~</event>~',
        $dstFileName
    );
    foreach ($fragments as $fragment) {
        $eventParams = [];

        if (preg_match('~<event\s+timestamp="(\d+)".+>~U', $fragment, $m)) {
            $eventParams[0] = $m[1];
        }
        if (preg_match('~<id>(.+)/(\d+)</id>~', $fragment, $m)) {
            $eventParams[1] = $m[1];
            $eventParams[2] = $m[2];
        }

        echo implode(',', $eventParams) . PHP_EOL;
    }
} catch (\ProfIT\Bbb\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
};