<?php

namespace ProfIT\Bbb\Layout;

use ProfIT\Bbb\Events\ChatEvent;
use ProfIT\Bbb\Events;
use Runn\Core\Collection;
use Runn\Core\Std;

class Layout
{
    protected $data;

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

    public function __construct(string $filename, string $name, StyleSheet $styles)
    {
        /** @var \SimpleXMLElement $xml */
        $xml = @simplexml_load_file($filename);
        if (false === $xml) {
            throw new \Exception('Layout file can not be loaded: ' . $filename);
        }

        $data = @$xml->xpath('//layouts/layout[@name="bbb.layout.name.' . $name . '"]');

        if (false === $data) {
            throw new \Exception('Invalid layout');
        }

        $this->styles = $styles;
        $this->data = $data[0];
    }

    /**
     * Set layout main window dimensions.
     *
     * @param int $width
     * @param int $height
     * @param int $pad
     */
    public function setDimensions(int $width, int $height, int $pad)
    {
        $this->width = $width;
        $this->height = $height;
        $this->pad = $pad;

        $this->makeWindows();
    }

    /**
     * Create inner windows.
     * Based on self XML data.
     */
    protected function makeWindows()
    {
        $windows = [];

        foreach ($this->data->window as $window) {
            /** @var \SimpleXMLElement $window */
            $attributes = $window->attributes();
            if (
                empty($attributes->width)
                ||
                false === in_array((string)$attributes->name, array_keys(Window::TITLES))
            ) {
                continue;
            }

            $windows[] = new Window($this->styles, new Std([
                'name'   => (string)$attributes->name,
                'x'      => (int) round(((float)$attributes->x) * $this->width),
                'y'      => (int) round(((float)$attributes->y) * $this->height),
                'w'      => (int) round(((float)$attributes->width) * $this->width),
                'h'      => (int) round(((float)$attributes->height) * $this->height),
                'hidden' => $attributes->hidden == true,
                'pad'    => $this->pad,
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
    protected function createWindowWithParams(array $params) {
        return new Window($this->styles, new Std([
            'x'      => (int) round(((float)$params['x']) * $this->width),
            'y'      => (int) round(((float)$params['y']) * $this->height),
            'w'      => (int) round(((float)$params['w']) * $this->width),
            'h'      => (int) round(((float)$params['h']) * $this->height),
            'pad'    => $this->pad,
        ]));
    }

    /**
     * Add custom window, based on input params, to object's windows property.
     *
     * @param array $params
     */
    public function addCustomWindow(array $params)
    {
        $customWindow = $this->createWindowWithParams($params);
        $customWindow->name = $params['name'];
        $this->windows[] = $customWindow;
    }

    /**
     * Add window, containing list of texts, to object's windows property.
     *
     * @param array $params
     * @param string[] $list
     */
    public function addListWindow(array $params, array $list)
    {
        $listWindow = $this->createWindowWithParams($params);
        foreach ($list as $text) {
            $listWindow->createUserListRow($text);
        }
        $this->windows[] = $listWindow;
    }

    /**
     * Add window, containing list of chat texts, to object's windows property.
     *
     * @param array $params
     * @param Collection|ChatEvent[] $messages
     * @param \ProfIT\Bbb\Events $events
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
     */
    public function setMarkedWindows(array $marked) {
        $this->markedWindowsNames = $marked;
    }

    /**
     * Set names of unfilled windows for this layout.
     *
     * @param string[] $unfilled
     */
    public function setUnfilledWindows(array $unfilled) {
        $this->unfilledWindowsNames = $unfilled;
    }

    /**
     * Generate image in PNG format for this layout.
     *
     * @param $dstFileName
     * @param bool $fillContent
     * @param bool $drawTitle
     * @param bool $bgTransparent
     */
    public function generatePng($dstFileName, bool $fillContent, bool $drawTitle = true, bool $bgTransparent = false)
    {
        $canvas = imagecreatetruecolor($this->width, $this->height);

        if (true === $bgTransparent) {
            $transparency = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefill($canvas, 0, 0, $transparency);
            imagesavealpha($canvas, true);
        } else {
            imagefill($canvas, 0, 0, Box::color($canvas, $this->styles->rules['Application']['backgroundColor']));
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
    }

}