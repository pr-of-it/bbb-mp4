<?php

require __DIR__ . '/autoload.php';

try {
    $src = __DIR__ . '/caa4040afddd103cde38f50e1ed596b42ef073c8-1458845168792/events.xml';
    //$src = __DIR__ . '/fb6d4c6057e78efdcffbe1de934109b988b68535-1438707313571/events.xml';
    $evF = new ProfIT\Bbb\EventsFile($src);

    $startPattern = '~<event\s+timestamp="\d+".*eventname="[A-Za-z]+Event">~';
    $endPattern = '~</event>~';

    $generator = $evF->extractFragments($startPattern, $endPattern);
    foreach ($generator as $eventFragment) {
        echo $eventFragment;
    }
} catch(\ProfIT\Bbb\Exception $e) {
    halt($e->getMessage() . PHP_EOL);
}