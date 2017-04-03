<?php

namespace ProfIT\Bbb;

/**
 * Class Events
 * @package ProfIT\Bbb
 */
class Events
{
    use TEventsExtract;

    protected $eventsFileName;

    /**
     * @param string $src - full path to events.xml
     * @throws \Exception
     */
    public function __construct(string $src)
    {
        $srcFileName = realpath($src);

        if (!is_readable($srcFileName)) {
            throw new \Exception('File does not exist or is not readable');
        }

        $this->eventsFileName = $srcFileName;
    }

    /**
     * @param $startPattern - regexp for fragment beginning
     * @param $endPattern - regexp for fragment end
     * @param $dstFileName - path to file where not suitable for fragment strings will be saved
     * @return \Generator - fragment generator
     * 
     * @throws \Exception
     */
    protected function extractFragments($startPattern, $endPattern, $dstFileName = null)
    {
        $src = fopen($this->eventsFileName, 'r');
        if (false === $src) {
            throw new \Exception ('Error while opening file: ' . $this->eventsFileName);
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
     * @return mixed|null
     *
     * @param string $pattern
     *
     * @throws \Exception
     */
    public function findValueByPattern(string $pattern) {
        $src = fopen($this->eventsFileName, 'r');
        if (false === $src) {
            throw new \Exception ('Error while opening file: ' . $this->eventsFileName);
        }

        while (false !== $line = fgets($src, 10240)) {
            if (preg_match($pattern, $line, $m)) {
                $timestamp = $m[1];
                fclose($src);
                return $timestamp;
            }
        }

        fclose($src);
        return null;
    }

    /**
     * @param string $eventName
     * 
     * @return string|null - timestamp or null
     */
    public function findFirstTimestamp($eventName = null) {
        $eventPart = empty($eventName) ? '' : (' eventname="' . $eventName . '"');
        return $this->findValueByPattern('~<event\s+timestamp="(\d+)".+' .$eventPart . '>~U');
    }

    /**
     * @return string|null - timestamp or null
     */
    public function findRealFirstTimestamp() {
        return $this->findValueByPattern('~<recording\s+meeting_id=".+\-(\d{10})\d+".+>~U');
    }

    /**
     * @return string|null - meeting name or null
     */
    public function findMeetingName() {
        return $this->findValueByPattern('~<metadata.+meetingName="(.+)".+>~U');
    }

    /**
     * @return string|null - meeting id or null
     */
    public function findMeetingId() {
        return $this->findValueByPattern('~<recording.+meeting_id="(.+)".+>~U');
    }

    /**
     * @param double $timestamp - relative timestamp
     *
     * @return \DateTime - absolute datetime object
     */
    public function getAbsoluteTime($timestamp) {
        $firstTimestamp = $this->findFirstTimestamp();
        $realFirstTimestamp = $this->findRealFirstTimestamp();
        
        $time = new \DateTime('@' . ($realFirstTimestamp + round(($timestamp - $firstTimestamp)/1000)));
        $time->setTimezone(new \DateTimeZone('Europe/Moscow'));
        return $time;
    }
}
