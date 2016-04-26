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

    public function extractCursorEvents($newFileName)
    {
        $startPattern = '~<event\s+timestamp="(\d+)".+eventname="([A-Za-z]+Event)">~';
        $endPattern = '~</event>~';

        $res = fopen($this->eventsFileName, 'r');
        $newRes = fopen($newFileName, 'w'); // для записи в конец нового файла

        if (false === $res) {
            throw new \ProfIT\Bbb\Exception ('Ошибка открытия файла: ' . $this->eventsFileName);
        }

        $writeFile = false; // Сигнал для записи в файл
        $captured = false; // Событие event не поймано
        $buffer = []; // временный массив для хранения значений [timestamp, x, y]
        $eventFragment = [];
        while (false !== $line = fgets($res, 10240)) {

            if (preg_match($startPattern, $line, $m)) { // Начало event
                $captured = $m[2];
                if (false === $captured) {
                    throw new \ProfIT\Bbb\Exception ('Unknown event: ' . $line);
                }

                switch($captured) {
                    case 'CursorMoveEvent':
                        $timestamp = $m[1];
                        $buffer[0] = $timestamp;

                        /* не попадаю в это блок */
                        if (preg_match('~<xOffset>([\d\.]+)</xOffset>~', $line, $m)) {
                            $buffer[2] = $m[1];
                            continue;
                        }
                        /* не попадаю в это блок */
                        if (preg_match('~<yOffset>([\d\.]+)</yOffset>~', $line, $m)) {
                            $buffer[2] = $m[1];
                            continue;
                        }
                        break;
                    default:
                        $eventFragment[] = $line; // для события, отличного от CursorMoveEvent
                        break;
                }

            } elseif (preg_match($endPattern, $line, $m)) { // потерялся закрывающийся </event>
                if (count($eventFragment) > 0) {
                    $writeFile = true; // можно записывать в файл
                    $eventFragment[] = $line;
                } else {
                    ksort($buffer);
                    echo implode(', ', $buffer) . PHP_EOL;
                    $buffer = [];
                    $captured = false;
                    continue;
                }

            } else {
                $writeFile = true; // запись строк
                $eventFragment[] = $line;
            }

            /** Запись в файл */
            if ($writeFile) {
                fwrite($newRes, implode('', $eventFragment));
                $eventFragment = [];
                $writeFile = false;
            }
        }

        fclose($res);
        fclose($newRes);
    }
}