<?php

namespace ProfIT\Bbb;

use ProfIT\Bbb\Layout\StyleSheet;
use ProfIT\Bbb\Layout\Window;

class Layout
{
    protected $data;
    protected $styles;
    protected $pad;

    const WINDOWS = [
        'PresentationWindow' => 'Презентация',
        'VideoDock'          => 'Веб-камера',
        'ChatWindow'         => 'Чат',
        'UsersWindow'        => 'Пользователи',
    ];

    /**
     * Layout constructor.
     * @param string $filename
     * @throws \Exception
     */
    public function __construct(string $filename, string $name, StyleSheet $styles, $pad)
    {
        /**
         * @var \SimpleXMLElement $xml
         */
        $xml = @simplexml_load_file($filename);
        if (false === $xml) {
            throw new \Exception('Layout file can not be loaded: ' . $filename);
        }

        $data = @$xml->xpath('//layouts/layout[@name="bbb.layout.name.' . $name . '"]');

        if (false === $data) {
            throw new \Exception('Invalid layout');
        }

        $this->data = $data[0];
        $this->styles = $styles;
        $this->pad = $pad;
    }

    public function getWindows()
    {
        $windows = [];

        foreach ($this->data->window as $window) {
            /**
             * @var \SimpleXMLElement $window
             */
            $name = (string) $window->attributes()->name;
            if (!in_array($name, array_keys(self::WINDOWS))) continue;

            $attributes = $window->attributes();

            if (! $attributes->width || ! $attributes->height) continue;

            $windows[$name] = new Window($this->styles, [
                'name'   => $name,
                'relX'   => (float) $attributes->x,
                'relY'   => (float) $attributes->y,
                'relW'   => (float) $attributes->width,
                'relH'   => (float) $attributes->height,
                'minW'   => (int)   $attributes->minWidth ?: null,
                'minH'   => (int)   $attributes->minHeight ?: null,
                'hidden' => $attributes->hidden == true,
                'pad'    => $this->pad,
            ]);
        }

        return $windows;
    }

}