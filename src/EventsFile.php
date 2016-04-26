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

        if (false === $res) {
            throw new \ProfIT\Bbb\Exception ('Ошибка открытия файла: ' . $this->eventsFileName);
        }

        $eventFragment = [];
        while (false !== $line = fgets($res, 10240)) {
            if (preg_match($startPattern, $line, $m)) {
                $eventFragment[] = $m[0];
            } elseif (preg_match($endPattern, $line, $m)) {
                $eventFragment[] = $m[0];
                yield implode(PHP_EOL, $eventFragment);
                $eventFragment = [];
            } elseif (count($eventFragment) > 0) { // если продолжение фрагмента
                $eventFragment[] = $line;
            }
        }

        fclose($res);
    }

    /**
     * @param $startPattern - регулярное выражение начала блока фрагмента
     * @param $endPattern - регулярное выражение конца блока фрагмента
     * @param $newFileName - файл для записи всех событий, кроме курсорных (CursorMoveEvent)
     * @param $event - событие по умолчанию 'CursorMoveEvent'
     * @throws Exception
     */
    public function extractFragments($startPattern, $endPattern, $newFileName, $event = 'CursorMoveEvent')
    {
        $res = fopen($this->eventsFileName, 'r');
        $newRes = fopen($newFileName, 'w'); // для записи в конец нового файла

        if (false === $res) {
            throw new \ProfIT\Bbb\Exception ('Ошибка открытия файла: ' . $this->eventsFileName);
        }

        $eventFragment = [];
        $buffer = []; // для csv формата

        while (false !== $line = fgets($res, 10240)) {
            if (preg_match($startPattern, $line, $m)) {
                $eventFragment[] = $m[0] . PHP_EOL;
            } elseif (preg_match($endPattern, $line, $m)) {
                $eventFragment[] = $m[0] . PHP_EOL;

                // Проверка на курсорное событие
                if (preg_match('~<event\s+timestamp="(\d+)".+eventname="'. $event .'">~', $eventFragment[0], $m)) {
                    $buffer[0] = $m[1]; // timestamp
                    // Если последовательность тегов для X и Y меняется
                    if (preg_match('~<xOffset>([\d\.]+)</xOffset>~', $eventFragment[1], $m)) {
                        $buffer[1] = $m[1];
                    } elseif (preg_match('~<yOffset>([\d\.]+)</yOffset>~', $eventFragment[1], $m)) {
                        $buffer[2] = $m[1];
                    }
                    // Если последовательность тегов для X и Y меняется
                    if (preg_match('~<yOffset>([\d\.]+)</yOffset>~', $eventFragment[2], $m)) {
                        $buffer[2] = $m[1];
                    } elseif (preg_match('~<xOffset>([\d\.]+)</xOffset>~', $eventFragment[2], $m)) {
                        $buffer[1] = $m[1];
                    }
                    ksort($buffer);
                    echo implode(',', $buffer) . PHP_EOL; // вывод в формате CSV "timestamp,x,y"
                    $buffer = [];

                    $eventFragment = [];
                    continue;
                } else {
                    fwrite($newRes, implode('', $eventFragment));
                    $eventFragment = [];
                }
            } else {
                $eventFragment[] = $line;
            }
        }
        fwrite($newRes, implode('', $eventFragment)); // закрывающий тег </recording>

        fclose($res);
        fclose($newRes);
    }
}