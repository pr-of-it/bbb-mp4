<?php

namespace ProfIT\Bbb;

use ProfIT\Bbb\Events\ChatEvent;
use ProfIT\Bbb\Events\DeskshareEvent;
use ProfIT\Bbb\Events\PresentationEvent;
use ProfIT\Bbb\Events\UserEvent;
use ProfIT\Bbb\Events\VoiceEvent;
use ProfIT\Bbb\Events\WebcamEvent;
use Runn\Core\Collection;
use Runn\Fs\File;

/** @mixin Events */
trait TEventsExtract
{
    /**
     * @param string $fragmentStart
     * @param callable $fillEvent
     * @return Collection
     */
    protected function extractEvents(string $fragmentStart, callable $fillEvent): Collection
    {
        $fragments = $this->extractFragments($fragmentStart, '~</event>~');

        $events = new Collection();

        foreach ($fragments as $fragment) {
            $event = $fillEvent($fragment);
            if (false === $event) {
                continue;
            }
            $events->append($event);
        }

        return $events;
    }

    /**
     * @return Collection
     */
    public function extractVoiceEvents(): Collection
    {
        return $this->extractEvents(
            '~<event.+module="VOICE"\s+eventname="\w+RecordingEvent">~',
            function (string $fragment) {
                $event = new VoiceEvent();
                preg_match('~<event\s+timestamp="(\d+)".+eventname="(\w+)RecordingEvent">~', $fragment, $m);
                $event->type = lcfirst($m[2]);
                $event->time = (double)$m[1];
                preg_match('~<filename>(.+)</filename>~', $fragment, $m);
                $event->file = $m[1];
                return $event;
            }
        );
    }

    /**
     * @param string $presentationPath
     * @return Collection
     * @throws \Exception
     */
    public function extractPresentationEvents(string $presentationPath): Collection
    {
        $path = new File($presentationPath);
        if (!$path->isDir() || !$path->isReadable()) {
            throw new \Exception('Directory does not exist or is not readable');
        }

        return $this->extractEvents(
            '~<event.+eventname="GotoSlideEvent">~',
            function (string $fragment) use ($path) {
                $event = new PresentationEvent();
                preg_match('~<event\s+timestamp="(\d+)".+>~U', $fragment, $m);
                $event->time = (double)$m[1];
                preg_match('~<id>(.+)/(\d+)</id>~', $fragment, $m);
                $event->file = $path->getPath() . '/' . $m[1] . '/' . $m[1] . '.pdf';
                $event->slide = (int)$m[2];
                return $event;
            }
        );
    }

    /**
     * @return Collection
     */
    public function extractUserEvents(): Collection
    {
        return $this->extractEvents(
            '~<event.+module="PARTICIPANT"\s+eventname="Participant(Left|Join)Event">~',
            function (string $fragment) {
                $event = new UserEvent();
                preg_match('~<event\s+timestamp="(\d+)".+eventname="Participant(\w+)Event">~', $fragment, $m);
                $event->type = lcfirst($m[2]);
                $event->time = $m[1];
                preg_match('~<userId>(\w+)</userId>~', $fragment, $m);
                $event->uid = $m[1];
                preg_match('~<name>(.+)</name>~u', $fragment, $m);
                $event->uname = $m[1] ?? '';
                return $event;
            }
        );
    }

    /**
     * @return Collection
     */
    public function extractChatEvents(): Collection
    {
        return $this->extractEvents(
            '~<event.+eventname="PublicChatEvent">~',
            function (string $fragment) {
                $event = new ChatEvent();
                preg_match('~<event\s+timestamp="(\d+)".+>~U', $fragment, $m);
                $event->time = $m[1];
                preg_match('~<sender>(.+)</sender>~', $fragment, $m);
                $event->user = $m[1];
                preg_match('~<message>.+<!\[CDATA\[(.+)\]\]>.+</message>~ms', $fragment, $m);
                if (isset($m[1])) {
                    $event->message = $m[1];
                } else {
                    return false;
                }
                preg_match('~<u>(.+)</u>~', $event->message, $m);
                if (isset($m[1])) {
                    $event->message = $m[1];
                }
                return $event;
            }
        );
    }

    /**
     * @return Collection
     */
    public function extractWebcamEvents(): Collection
    {
        return $this->extractEvents(
            '~<event.+module="WEBCAM"\s+eventname="(Start|Stop)WebcamShareEvent">~',
            function (string $fragment) {
                $event = new WebcamEvent();
                preg_match('~<event\s+timestamp="(\d+)".+eventname="(\w+)WebcamShareEvent">~', $fragment, $m);
                $event->type = lcfirst($m[2]);
                $event->time = $m[1];
                preg_match('~<stream>(.+)</stream>~', $fragment, $m);
                $event->file = $m[1];
                return $event;
            }
        );
    }

    /**
     * @return Collection
     */
    public function extractDeskshareEvents(): Collection
    {
        return $this->extractEvents(
            '~<event.+module="Deskshare"\s+eventname="Deskshare\w+Event">~',
            function (string $fragment) {
                $event = new DeskshareEvent();
                preg_match('~<event\s+timestamp="(\d+)".+eventname="Deskshare(\w+)Event">~', $fragment, $m);
                $event->type = lcfirst($m[2]);
                $event->time = $m[1];
                preg_match('~<file>(.+)</file>~', $fragment, $m);
                $event->file = $m[1];
                return $event;
            }
        );
    }
}