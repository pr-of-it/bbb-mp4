<?php

/**
 * @use php extractEvents.php --type=(cursor|voice) --src=events.xml --dst=events.new.xml > cursor.events
 */

$options = getopt('', ['type:', 'src:', 'dst:']);
$eventsFileName = realpath($options['src']);
$newFileName    = $options['dst'];
$type = $options['type'];

$s = fopen($eventsFileName, 'r');
$d = fopen($newFileName, 'w');
$passthru = true;
$captured = false;
$buffer = [];

switch ($type) {
    case 'cursor': $nameRe = 'CursorMoveEvent';         break;
    case 'voice':  $nameRe = 'ParticipantTalkingEvent'; break;
    default: throw new Exception('Unknown event type: '. $type);
}

while (false !== $line = fgets($s, 10240) ) {
    if (preg_match('~<event\s+timestamp="(\d+)".+eventname="('. $nameRe. ')">~', $line, $m)) {
        $timestamp = $m[1];
        $passthru = false;
        $captured = $m[2];
        $buffer[0] = $timestamp;
        continue;
    }

    if ($captured) {
        switch ($captured) {
            case 'CursorMoveEvent':
                if (preg_match('~<xOffset>([\d\.]+)</xOffset>~', $line, $m)) {
                    $buffer[1] = $m[1];
                    continue;
                }

                if (preg_match('~<yOffset>([\d\.]+)</yOffset>~', $line, $m)) {
                    $buffer[2] = $m[1];
                    continue;
                }

            break;

            case 'ParticipantTalkingEvent':
                if (preg_match('~<participant>(\w+)</participant>~', $line, $m)) {
                    $buffer[1] = $m[1];
                    continue;
                }

                if (preg_match('~<bridge>(\d+)</bridge>~', $line, $m)) {
                    $buffer[2] = $m[1];
                    continue;
                }

                if (preg_match('~<talking>(true|false)</talking>~', $line, $m)) {
                    $buffer[3] = (int) $m[1] == 'true';
                    continue;
                }

            break;
        }

        if (preg_match('~</event>~', $line)) {
            ksort($buffer);
            echo implode(', ', $buffer) . "\n";
            $buffer = [];
            $captured = false;
            $passthru = true;
            continue;
        }
    }

    if ($passthru) {
        fwrite($d, $line);
    }
}
fclose($s);
fclose($d);