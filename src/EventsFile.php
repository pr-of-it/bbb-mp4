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
     * @param $dstFileName - путь к файлу для вывода строк, не соответствующих фрагменту
     * @return \Generator - генератор фрагментов
     * 
     * @throws \ProfIT\Bbb\Exception
     */
    public function extractFragments($startPattern, $endPattern, $dstFileName = null)
    {
        $src = fopen($this->eventsFileName, 'r');
        if (false === $src) {
            throw new \ProfIT\Bbb\Exception ('Error while opening file: ' . $this->eventsFileName);
        }
        
        if (!empty($dstFileName)) {
            $dst = fopen($dstFileName, 'w');
        }

        $eventFragment = [];
        $capture = false;
        
        while (false !== $line = fgets($src, 10240)) {
            if (preg_match($startPattern, $line, $m)) {
                $eventFragment[] = $m[0];
                $capture = true;
            } elseif (preg_match($endPattern, $line, $m) && $capture) {
                $eventFragment[] = $m[0];
                yield implode(PHP_EOL, $eventFragment);
                $eventFragment = [];
                $capture = false;
            } elseif ($capture) {
                $eventFragment[] = $line;
            } elseif (isset($dst)) {
                fwrite($dst, $line);
            }
        }

        fclose($src);
        if (isset($dst)) {
            fclose($dst);
        }
    }

    /**
     * @param string $eventName - название события для поиска
     * 
     * @return string|null - отметка времени или null
     *
     * @throws \ProfIT\Bbb\Exception
     */
    public function findFirstTimestamp($eventName = null) {
        $src = fopen($this->eventsFileName, 'r');
        if (false === $src) {
            throw new \ProfIT\Bbb\Exception ('Error while opening file: ' . $this->eventsFileName);
        }

        while (false !== $line = fgets($src, 10240)) {
            $eventPart = empty($eventName) ? '' : (' eventname="' . $eventName . '"');
            $pattern = '~<event\s+timestamp="(\d+)".+' .$eventPart . '>~U';
            if (preg_match($pattern, $line, $m)) {
                $timestamp = $m[1];
                fclose($src);
                return $timestamp;
            }
        }

        fclose($src);
        return null;
    }
    
}