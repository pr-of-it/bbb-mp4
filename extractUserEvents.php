<?php
/**
 * @use php extractUserEvents.php --src=events.xml > user.events
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
        '~<event.+module="PARTICIPANT"\s+eventname="Participant(Left|Join)Event">~',
        '~</event>~'
    );

    foreach ($fragments as $fragment) {
        $eventParams = [];

        preg_match('~<event\s+timestamp="(\d+)".+eventname="Participant(\w+)Event">~', $fragment, $m);
            $eventParams[0] = $m[2];
            $eventParams[1] = $m[1];
        preg_match('~<userId>(\w+)</userId>~', $fragment, $m);
            $eventParams[2] = $m[1];
        preg_match('~<name>(.+)</name>~u', $fragment, $m);
            $eventParams[3] = $m[1];
        
        if ('Left' == $eventParams[0]) {
            $eventParams[3] = '';
        }

        echo implode(',', $eventParams) . PHP_EOL;
    }

} catch (\ProfIT\Bbb\Exception $e) {
    halt($e->getMessage() . PHP_EOL);
}