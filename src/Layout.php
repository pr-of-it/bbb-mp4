<?php

namespace ProfIT\Bbb;

class Layout
{
    protected $data;

    protected $activeWindows = ['PresentationWindow', 'VideoDock', 'ChatWindow', 'UsersWindow'];

    /**
     * Layout constructor.
     * @param string $filename
     * @throws \Exception
     */
    public function __construct(string $filename, string $name)
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
    }

    public function getWindows()
    {
        $windows = [];

        foreach ($this->data->window as $window) {
            /**
             * @var \SimpleXMLElement $window
             */
            $name = (string) $window->attributes()->name;
            if (!in_array($name, $this->activeWindows)) continue;

            $attributes = $window->attributes();

            if (! $attributes->width || ! $attributes->height) continue;

            $windows[$name] = new Layout\Window([
                'name'   => $name,
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

    public function setStyleSheet($filename)
    {
        $styleSheet = new Style\Sheet($filename);
    }
}