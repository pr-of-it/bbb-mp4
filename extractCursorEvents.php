<?php
/**
 * @use php extractCursorEvents.php --events-file-src=events.xml --events-file-dst=events.new.xml > cursor.events
 */

//$options = getopt('', ['src:', 'dst:']);
//$eventsFileName = realpath($options['src']);
//$newFileName    = $options['dst'];
$newFileName = __DIR__ . '/events.new.xml';

require __DIR__ . '/autoload.php';

try {
    //$src = __DIR__ . '/caa4040afddd103cde38f50e1ed596b42ef073c8-1458845168792/events.xml';
    //$src = __DIR__ . '/fb6d4c6057e78efdcffbe1de934109b988b68535-1438707313571/events.xml';

    $src =  __DIR__ . '/events.xml';

    $evCursors = new ProfIT\Bbb\EventsFile($src);
    $evCursors->extractCursorEvents($newFileName);

} catch(\ProfIT\Bbb\Exception $e) {
    echo $e->getMessage();
}