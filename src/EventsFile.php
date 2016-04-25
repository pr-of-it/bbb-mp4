<?php

namespace ProfIT\Bbb;

/**
 * Class EventsFile
 * @package ProfIT\Bbb
 */
class EventsFile
{
    protected $eventsFileName;

    /**
     * @param string $src - полный путь до events.xml
     */
    public function __construct(string $src)
    {
        $this->eventsFileName = $src;
    }

    /**
     * @param $startFrag - регулярное выражение начала блока фрагмента
     * @param $endFrag - регулярное выражение конца блока фрагмента
     * @return \Generator - генератор фрагментов
     */
    public function generateFragment($startFrag, $endFrag)
    {
        $res = fopen($this->eventsFileName, 'r');

        if (false !== $res) {
            $eventFragment = [];
            while (false !== $line = fgets($res, 10240)) {
                if (preg_match($startFrag, $line, $m)) {
                    $eventFragment [] = $m[0];
                } elseif (preg_match($endFrag, $line, $m)) {
                    $eventFragment [] = $m[0];
                    yield implode('', $eventFragment);
                    $eventFragment  = [];
                } elseif (count($eventFragment) > 0) { // если продолжение фрагмента
                    $eventFragment [] = $line;
                }
            }
        }

        fclose($res);
    }
}