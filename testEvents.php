<?php

require __DIR__ . '/autoload.php';

$src = __DIR__ . '/caa4040afddd103cde38f50e1ed596b42ef073c8-1458845168792/events.xml';
//$src = __DIR__ . '/fb6d4c6057e78efdcffbe1de934109b988b68535-1438707313571/events.xml';
$evF = new ProfIT\Bbb\EventsFile($src);

$startFrag = '~<event\s+timestamp="\d+".*eventname="[A-Za-z]+Event">~';
$endFrag = '~</event>~';

$generator = $evF->generateFragment($startFrag, $endFrag);
foreach ($generator as $eventFragment) {
    echo $eventFragment;
}