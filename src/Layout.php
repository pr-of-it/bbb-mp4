<?php

namespace ProfIT\Bbb;

use ProfIT\Bbb\StyleSheet;

class Layout
{
    protected $data;
    protected $styles;

    protected $activeWindows = [
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
    public function __construct(string $filename, string $name, StyleSheet $styles)
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
    }

    public function getWindows()
    {
        $windows = [];

        foreach ($this->data->window as $window) {
            /**
             * @var \SimpleXMLElement $window
             */
            $name = (string) $window->attributes()->name;
            if (!in_array($name, array_keys($this->activeWindows))) continue;

            $attributes = $window->attributes();

            if (! $attributes->width || ! $attributes->height) continue;

            $windows[$name] = new Window($this->styles, [
                'name'   => $this->activeWindows[$name],
                'relX'   => (float) $attributes->x,
                'relY'   => (float) $attributes->y,
                'relW'   => (float) $attributes->width,
                'relH'   => (float) $attributes->height,
                'minW'   => (int)   $attributes->minWidth ?: null,
                'minH'   => (int)   $attributes->minHeight ?: null,
                'hidden' => $attributes->hidden == true,
                'pad'    => 2
            ]);
        }

        return $windows;
    }

}