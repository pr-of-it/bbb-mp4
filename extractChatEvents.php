<?php
/**
 * @use php extractChatEvents.php --src=events.xml --dst=events.new.xml > chat.events
 */
require __DIR__ . '/autoload.php';
require __DIR__ . '/functions.php';

$options = getopt('', ['path:', 'pdf:', 'src:', 'dst:']);
$srcFileName = realpath($options['src']);
$dstFileName = $options['dst'];

$events = new \ProfIT\Bbb\EventsFile($srcFileName);

try {
    $fragments = $events->extractFragments(
        '~<event.+eventname="PublicChatEvent">~',
        '~</event>~',
        $dstFileName
    );
    foreach ($fragments as $fragment) {
        $eventParams = [];

        preg_match('~<event\s+timestamp="(\d+)".+>~U', $fragment, $m);
        $eventParams[0] = $m[1];

        preg_match('~<sender>(.+)</sender>~', $fragment, $m);
        $eventParams[1] = $m[1];

        preg_match('~<message>.+<!\[CDATA\[(.+)\]\]>.+</message>~ms', $fragment, $m);
        $eventParams[2] = $m[1];
        preg_match('~<u>(.+)</u>~', $eventParams[2], $m);
        if (isset($m[1])) {
            $eventParams[2] = $m[1];
        }

        fputcsv(STDOUT, $eventParams);
    }
} catch (\ProfIT\Bbb\Exception $e) {
    halt($e->getMessage() . PHP_EOL);
};