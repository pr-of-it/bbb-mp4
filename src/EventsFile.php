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
     * @param $startPattern - регулярное выражение начала блока фрагмента
     * @param $endPattern - регулярное выражение конца блока фрагмента
     * @return \Generator - генератор фрагментов
     */
    public function getFragments($startPattern, $endPattern)
    {
        $res = fopen($this->eventsFileName, 'r');

        if (!$res) {
            throw new \ProfIT\Bbb\Exception ('Ошибка открытия файла: ' . $this->eventsFileName);
        }

        $eventFragment = [];
        while (false !== $line = fgets($res, 10240)) {
            if (preg_match($startPattern, $line, $m)) {
                $eventFragment [] = $m[0];
            } elseif (preg_match($endPattern, $line, $m)) {
                $eventFragment [] = $m[0];
                yield implode('', $eventFragment);
                $eventFragment  = [];
            } elseif (count($eventFragment) > 0) { // если продолжение фрагмента
                $eventFragment [] = $line;
            }
        }

        fclose($res);
    }
}