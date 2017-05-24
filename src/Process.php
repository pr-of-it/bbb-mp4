<?php

namespace ProfIT\Bbb;

use ProfIT\Bbb\Layout\Layout;
use ProfIT\Bbb\SOX\Sound;
use Runn\Core\Config;

/**
 * Class Process
 * @package ProfIT\Bbb
 *
 * @property string $src
 * @property string $dst
 */
class Process
{
    use TImageFunctions;
    use TFilterFunctions;

    /** @var \Runn\Core\Config $config */
    protected $config;
    /** @var \ProfIT\Bbb\FFMpeg $ffmpeg */
    protected $ffmpeg;
    /** @var \ProfIT\Bbb\Events $events */
    protected $events;
    /** @var \ProfIT\Bbb\Layout\Layout $layout */
    protected $layout;
    protected $layoutImageName = 'layout.png';

    /** @var  \ProfIT\Bbb\SOX\Sound $sound */
    protected $sound;

    /**
     * Process constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->checkConfig();
        $this->ffmpeg = new FFMpeg();

        $this->src = realpath($config->paths->source) . '/';

        if (!is_readable($config->paths->destination)) {
            mkdir($config->paths->destination, 0777);
        }
        $this->dst = realpath($config->paths->destination) . '/';

        $this->events = new Events($this->src . 'events.xml');
    }

    public function run()
    {
        /** Prepare layout */
        $this->log('...preparing layout');
        $this->layout = new Layout($this->config->layout);
        $this->layout
            ->useBackground()
            ->setUnfilledWindows(['PresentationWindow', 'VideoDock'])
            ->generatePng($this->dst . $this->layoutImageName);

        /** Prepare sound */
        $this->log('...preparing sound');
        $voiceEvents = $this->events->extractVoiceEvents();
        $this->sound = new Sound($voiceEvents, $this->src . 'audio');
        $this->sound->export($this->dst . 'sound.wav');

        /** Initial sources */
        $this->ffmpeg->addSoundSource($this->sound->getExportFile());
        $this->ffmpeg->addLoopImageSource($this->dst . $this->layoutImageName);

        /** Prepare presentation */
        $this->log('...preparing presentation slides');
        $presentationEvents = $this->events->extractPresentationEvents($this->src . 'presentation');
        $presentationFiles = array_unique(array_column($presentationEvents->toArray(), 'file'));
        $this->generatePresentationImages($presentationFiles);
        $this->preparePresentationFilters($presentationEvents);

        /** Prepare user list */
        $this->log('...preparing user list');
        $userEvents = $this->events->extractUserEvents();
        $this->prepareUserFilters($userEvents);

        /** Prepare chat list */
        $this->log('...preparing chat list');
        $chatEvents = $this->events->extractChatEvents();
        $this->prepareChatFilters($chatEvents);

        /** Prepare webcam fragments */
        $this->log('...preparing webcam fragments');
        $webcamEvents = $this->events->extractWebcamEvents();
        $this->prepareWebcamFilters($webcamEvents);

        /** Prepare deskshare fragments */
        $this->log('...preparing deskshare fragments');
        $deskshareEvents = $this->events->extractDeskshareEvents();
        $this->prepareDeskshareFilters($deskshareEvents);

        /** Combine video */
        $this->log('...combining video');
        $this->ffmpeg->export($this->dst, true === $this->config->log);
    }

    protected function log($message)
    {
        if (true === $this->config->log) {
            echo $message . PHP_EOL;
        }
    }

    protected function error($message)
    {
        if (true === $this->config->log) {
            fwrite(STDERR, $message . PHP_EOL);
            exit(1);
        } else {
            throw new \Exception($message);
        }
    }

    protected function checkConfig()
    {
        if (!is_readable(realpath($this->config->paths->resources))) {
            $this->error('Resources directory does not exist or is not readable');
        }

        if (!is_readable(realpath($this->config->paths->source))) {
            $this->error('Source directory does not exist or is not readable');
        }

        if (!is_readable($this->config->layout->styles)) {
            $this->error('Layout styles file can not be loaded: ' . $this->config->layout->styles);
        }

        if (!is_readable($this->config->layout->background)) {
            $this->error('Layout background file can not be loaded: ' . $this->config->layout->background);
        }

        $output = [];
        $return_val = null;
        $imgData = exec(
            'magick identify -format "%m;%W;%H" ' . realpath($this->config->layout->background),
            $output,
            $return_val
        );
        if (0 == $return_val) {
            [$this->config->layout->imgType, $width, $height] = explode(';', $imgData);
            $this->config->layout->width = $this->config->layout->width ?? $width;
            $this->config->layout->height = $this->config->layout->height ?? $height;
        } else {
            $this->error('Unknown background image format of file: ' . $this->config->layout->background);
        }

        $xs = [];
        $ys = [];
        foreach ($this->config->layout->windows as $window) {
            $xs[] = $window->x + $window->width;
            $ys[] = $window->y + $window->height;
        }
        if ($this->config->layout->width < max($xs) || $this->config->layout->height < max($ys)) {
            $this->error('The windows should be located within the background');
        }
    }
}
