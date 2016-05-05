<?php

namespace ProfIT\Bbb\Layout;

use ProfIT\Bbb\Layout;

class Image
    extends Box
{
    public $x = 0;
    public $y = 0;

    protected $layout;

    public function loadLayout(Layout $layout)
    {
        $this->layout = $layout;
        $this->bgColor = $this->styles->rules['Application']['backgroundColor'];

        foreach ($layout->getWindows() as $child) {
            /** @var Window $child */
            $child->title = $child->name;
            $child->createTitleBar();

            $this->addChild($child);
        }
    }

    public function generatePng($filename)
    {
        $canvas = imagecreatetruecolor($this->absW, $this->absH);
        imagefill($canvas, 0, 0, self::color($canvas, $this->bgColor));

        $this->canvas = $canvas;

        $this->render($canvas);

        imagepng($canvas, $filename);
    }
}