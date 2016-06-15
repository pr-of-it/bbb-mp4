<?php
/**
 * @use php extractPresentationEvents.php --path=./presentationFilePath/ --src=events.xml --dst=events.new.xml > presentation.events
 */
require __DIR__ . '/autoload.php';
require __DIR__ . '/functions.php';

$options = getopt('', ['path:', 'pdf:', 'src:', 'dst:']);
$presentationFilePath = realpath($options['path']);
$srcFileName = realpath($options['src']);
$dstFileName = $options['dst'];

if (!is_readable($presentationFilePath)) {
    halt('Directory does not exist or is not readable');
}

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
            $eventParams[1] = $presentationFilePath . DS . $m[1] . DS . $m[1] . '.pdf';
            $eventParams[2] = $m[2];
        }

        fputcsv(STDOUT, $eventParams);
    }
} catch (\ProfIT\Bbb\Exception $e) {
    halt($e->getMessage() . PHP_EOL);
};