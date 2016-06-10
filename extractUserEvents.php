<?php
/**
 * @use php extractUserEvents.php --src=events.xml > user.events
 */
require __DIR__ . '/autoload.php';
require __DIR__ . '/functions.php';

$options = getopt('', ['src:']);
$srcFileName = realpath($options['src']);

$events = new \ProfIT\Bbb\EventsFile($srcFileName);

try {
    $fragments = $events->extractFragments(
        '~<event.+eventname="ParticipantJoinEvent">~',
        '~</event>~'
    );

    foreach ($fragments as $fragment) {
        $eventParams = [];

        $eventParams[0] = 'join';

        if (preg_match('~<event\s+timestamp="(\d+)".+>~U', $fragment, $m)) {
            $eventParams[1] = $m[1];
        }
        if (preg_match('~<userId>(\w+)</userId>~', $fragment, $m)) {
            $eventParams[2] = $m[1];
        }
        if (preg_match('~<name>(.+)</name>~', $fragment, $m)) {
            $eventParams[3] = $m[1];
        }

        echo implode(',', $eventParams) . PHP_EOL;
    }

    $fragments = $events->extractFragments(
        '~<event.+eventname="ParticipantLeftEvent">~',
        '~</event>~'
    );

    foreach ($fragments as $fragment) {
        $eventParams = [];

        $eventParams[0] = 'left';

        if (preg_match('~<event\s+timestamp="(\d+)".+>~U', $fragment, $m)) {
            $eventParams[1] = $m[1];
        }
        if (preg_match('~<userId>(\w+)</userId>~', $fragment, $m)) {
            $eventParams[2] = $m[1];
        }

        $eventParams[3] = 'name';
        

        echo implode(',', $eventParams) . PHP_EOL;
    }
} catch (\ProfIT\Bbb\Exception $e) {
    halt($e->getMessage() . PHP_EOL);
}