<?php

namespace ProfIT\Bbb\SOX;

use ProfIT\Bbb\Events\VoiceEvent;
use Running\Core\Collection;
use Running\Core\Std;

/**
 * Class Sound
 * @package ProfIT\Bbb\SOX
 */
class Sound
{
    /** @var Collection|VoiceEvent[]  */
    protected $events;

    /** @var string */
    protected $source;

    /** @var Collection|Fragment[] */
    protected $fragments;

    protected $startTime;
    protected $exportFile;

    public function __construct(Collection $events, string $source = null)
    {
        $this->events = $events;
        $this->source = $source;

        $this->fragments = $this->getFragments();
    }

    protected function getFragments(): Collection
    {
        $fragments = new Collection();

        foreach ($this->events as $event) {
            if ('start' !== $event->type) {
                continue;
            }

            $fragment = new Fragment();
            $fragment->source = empty($this->source) ? $event->file : $this->source . '/' . basename($event->file);

            if (!empty($this->startTime)) {
                $fragment->delay = ($event->time - $this->startTime) / 1000;
            } else {
                $fragment->delay = 0;
                $this->startTime = $event->time;
            }

            $fragments->add($fragment);
        }

        return $fragments;
    }

    public function export(string $dst)
    {
        if ($this->fragments->count() === 0) {
            throw new \Exception('Voice fragments not found');
        }

        /** @var string[] $fragmentStrings */
        $fragmentStrings = $this->fragments->map(function(Std $fragment) {
            return ' -v 1 "|sox ' . $fragment->source . ' -p pad ' . $fragment->delay . ' 0"';
        })->toArray();

        $this->exportFile = dirname($dst) . '/' . $this->startTime . '.' . basename($dst);

        exec('sox -m' . implode('', $fragmentStrings) . ' -b 16 -r 44100 -G ' . $this->exportFile);
    }

    public function getStartTime ()
    {
        return $this->startTime;
    }

    public function getExportFile ()
    {
        return $this->exportFile;
    }
}