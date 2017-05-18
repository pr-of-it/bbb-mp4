<?php

namespace ProfIT\Bbb\Layout;

use ProfIT\Bbb\Events\ChatEvent;
use ProfIT\Bbb\Events;
use Runn\Core\Collection;
use Runn\Core\Config;
use Runn\Core\Std;

class Layout
{
    /** @var StyleSheet */
    protected $styles;

    /** @var  int */
    protected $width;
    /** @var  int */
    protected $height;
    /** @var  int */
    protected $pad;

    /** @var Window[] */
    protected $windows = [];
    /** @var string[] */
    protected $markedWindowsNames = [];
    /** @var string[] */
    protected $unfilledWindowsNames = [];
    /** @var bool */
    protected $useBackground;
    /** @var  string */
    protected $imgFormat;
    /** @var  string */
    protected $backgroundFile;

    public function __construct(Config $layoutConfig)
    {
        $this->styles = new StyleSheet($layoutConfig->styles);
        $this->useBackground = false;
        $this->width = $layoutConfig->width;
        $this->height = $layoutConfig->height;
        $this->imgFormat = $layoutConfig->imgType;
        $this->backgroundFile = realpath($layoutConfig->background);
        $this->pad = 10;
        $this->windows = $layoutConfig->windows;
    }

    /**
     * Set layout main window dimensions.
     *
     * @param int $width
     * @param int $height
     * @param int $pad
     * @return $this
     */
    public function setDimensions(int $width, int $height, int $pad)
    {
        $this->width = $width;
        $this->height = $height;
        $this->pad = $pad;

        $this->makeWindows();

        return $this;
    }

    /**
     * Create inner windows.
     * Based on config.
     */
    protected function makeWindows()
    {
        $windows = [];

        foreach ($this->windows as $name => $window) {
            $windows[] = new Window($this->styles, new Std([
                'name' => $name,
                'x' => $window->x,
                'y' => $window->y,
                'w' => $window->width,
                'h' => $window->height,
                'hidden' => $window->hidden ?? false,
                'pad' => $this->pad,
            ]));
        }

        $this->windows = $windows;
    }

    /**
     * Create inner window.
     * Based on input params.
     *
     * @param array $params
     *
     * @return Window
     */
    protected function createWindowWithParams(array $params)
    {
        return new Window($this->styles, new Std([
            'x' => $params['x'] * $this->width,
            'y' => $params['y'] * $this->height,
            'w' => $params['w'] * $this->width,
            'h' => $params['h'] * $this->height,
            'pad' => $this->pad,
        ]));
    }

    /**
     * Add custom window, based on input params, to object's windows property.
     *
     * @param array $params
     * @return $this
     */
    public function addCustomWindow(array $params)
    {
        $customWindow = $this->createWindowWithParams($params);
        $customWindow->name = $params['name'];
        $this->windows[] = $customWindow;

        return $this;
    }

    /**
     * Add window, containing list of texts, to object's windows property.
     *
     * @param array $params
     * @param string[] $list
     * @return $this
     */
    public function addListWindow(array $params, array $list)
    {
        $listWindow = $this->createWindowWithParams($params);
        foreach ($list as $text) {
            $listWindow->createUserListRow($text);
        }
        $this->windows[] = $listWindow;

        return $this;
    }

    /**
     * Add window, containing list of chat texts, to object's windows property.
     *
     * @param array $params
     * @param Collection|ChatEvent[] $messages
     * @param \ProfIT\Bbb\Events $events
     * @return $this
     */
    public function addChatListWindow(array $params, Collection $messages, Events $events)
    {
        $meetingName = $events->findMeetingName();
        $listWindow = $this->createWindowWithParams($params);
        $listWindow->createChatListCaption($meetingName);
        foreach ($messages as $item) {
            $listWindow->createChatMessageCaption($item->user, $events->getAbsoluteTime($item->time)->format('H:i'));
            $listWindow->createChatMessage($item->message);
        }
        $this->windows[] = $listWindow;

        return $this;
    }

    /**
     * Get inner windows objects.
     *
     * @return Window[]
     */
    public function getWindows()
    {
        if (count($this->markedWindowsNames) > 0) {
            return array_filter($this->windows, function (Window $w) {
                return in_array($w->name, $this->markedWindowsNames);
            });
        } else {
            return $this->windows;
        }
    }

    /**
     * Get inner window by its name property.
     *
     * @param string $name
     * @throws \Exception
     *
     * @return Window
     */
    public function getWindowByName(string $name)
    {
        foreach ($this->getWindows() as $window) {
            if ($window->name === $name) {
                $result = $window;
                break;
            }
        }

        if (empty($result)) {
            throw new \Exception('Window with name "' . $name . '" not found');
        }

        return $result;
    }

    /**
     * Set names of marked windows for this layout.
     *
     * @param string[] $marked
     * @return $this
     */
    public function setMarkedWindows(array $marked)
    {
        $this->markedWindowsNames = $marked;

        return $this;
    }

    /**
     * Set names of unfilled windows for this layout.
     *
     * @param string[] $unfilled
     * @return $this
     */
    public function setUnfilledWindows(array $unfilled)
    {
        $this->unfilledWindowsNames = $unfilled;

        return $this;
    }

    /**
     * Generate image in PNG format for this layout.
     *
     * @param $dstFileName
     * @param bool $fillContent
     * @param bool $drawTitle
     * @param bool $bgTransparent
     * @return $this
     * @throws \Exception
     */
    public function generatePng($dstFileName, bool $fillContent, bool $drawTitle = true, bool $bgTransparent = false)
    {
        $canvas = imagecreatetruecolor($this->width, $this->height);
        if ($this->useBackground) {
            switch ($this->imgFormat) {
                case 'JPEG':
                    $background = imagecreatefromjpeg($this->backgroundFile);
                    break;
                case 'GIF':
                    $background = imagecreatefromgif($this->backgroundFile);
                    break;
                case 'TIFF':
                case 'BMP':
                case 'BMP2':
                case 'BMP3':
                    $tmpFile = tempnam('./', '');
                    unlink($tmpFile);
                    exec('convert ' . $this->backgroundFile . ' ' . $tmpFile . '.png');
                    $background = imagecreatefrompng($tmpFile . '.png');
                    unlink($tmpFile . '.png');
                    break;
                case 'PNG':
                    $background = imagecreatefrompng($this->backgroundFile);
                    break;
                default:
                    throw new \Exception('Unsupported image format: ' . $this->imgFormat);
            }
            imagecopyresized($canvas, $background, 0, 0, 0, 0, $this->width, $this->height, imagesx($background), imagesy($background));
        } else {
            if (true === $bgTransparent) {
                $transparency = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
                imagefill($canvas, 0, 0, $transparency);
                imagesavealpha($canvas, true);
            } else {
                imagefill($canvas, 0, 0, Box::color($canvas, $this->styles->rules['Application']['backgroundColor']));
            }
        }

        foreach ($this->windows as $window) {
            if (count($this->markedWindowsNames) > 0 && false === in_array($window->name, $this->markedWindowsNames)) {
                continue;
            }

            if (true === $drawTitle) {
                $window->createTitleBar();
            }

            if (true === $fillContent && false === in_array($window->name, $this->unfilledWindowsNames)) {
                $window->fillContentBackground();
            }

            $window->render($canvas);
        }

        imagepng($canvas, $dstFileName);

        return $this;
    }

    /**
     * Enable using background image to generate png file
     * @return $this
     */
    public function useBackground()
    {
        $this->useBackground = true;
        $this->makeWindows();

        return $this;
    }
}
