<?php
/**
 * @use php extractCursorEvents.php --events-file-src=events.xml --events-file-dst=events.new.xml > cursor.events
 */

$options = getopt('', ['events-file-src:', 'events-file-dst:']);
$src = realpath($options['events-file-src']);
$newFileName    = $options['events-file-dst'];

require __DIR__ . '/autoload.php';

try {
    $startPattern = '~<event\s+timestamp="\d+".*eventname="[A-Za-z]+Event">~';
    $endPattern = '~</event>~';

    $evCursors = new ProfIT\Bbb\EventsFile($src);
    $evCursors->extractFragments($startPattern, $endPattern, $newFileName);

} catch(\ProfIT\Bbb\Exception $e) {
    echo $e->getMessage();
}