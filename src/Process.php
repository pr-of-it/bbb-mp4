<?php

namespace ProfIT\Bbb;

use ProfIT\Bbb\Layout\Layout;
use ProfIT\Bbb\Layout\StyleSheet;
use ProfIT\Bbb\SOX\Sound;
use Running\Core\Config;

/**
 * Class Process
 * @package ProfIT\Bbb
 *
 * @property string $src
 * @property string $dst
 * @property int $width
 * @property int $height
 */
class Process
{
    use TImageFunctions;
    use TFilterFunctions;

    /** @var \Running\Core\Config $config */
    protected $config;
    /** @var \ProfIT\Bbb\FFMpeg $ffmpeg */
    protected $ffmpeg;
    /** @var \ProfIT\Bbb\Events $events */
    protected $events;
    /** @var \ProfIT\Bbb\Layout\StyleSheet $styles */
    protected $styles;

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
        $this->ffmpeg = new FFMpeg();

        $this->src = realpath($config->paths->source) . '/';
        if (!is_readable($this->src)) {
            $this->error('Source directory does not exist or is not readable');
        }

        $this->width = $config->video->width ?? 1280;
        $this->height = $config->video->height ?? 720;

        if (!is_readable($config->paths->destination)) {
            mkdir($config->paths->destination, 0777);
        }
        $this->dst = realpath($config->paths->destination) . '/';

        $this->events = new Events($this->src . 'events.xml');
        $this->styles = new StyleSheet($config->paths->resources . '/style/css/BBBDefault.css');
    }

    public function run()
    {
        /** Prepare layout */
        $this->log('...preparing layout');
        $this->layout = new Layout($this->config->paths->resources . '/layout.xml', 'defaultlayout', $this->styles);
        $this->layout->setDimensions($this->width, $this->height, 10);
        $this->layout->setUnfilledWindows(['PresentationWindow', 'VideoDock']);
        $this->layout->generatePng($this->dst . $this->layoutImageName, true);

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
}