<?php

namespace ProfIT\Bbb\Layout;

use ProfIT\Bbb\Layout;

class Image
    extends Box
{
    public $x = 0;
    public $y = 0;

    protected $layout;

    public function __construct($width, $height)
    {
        parent::__construct(null, ['w' => $width, 'h' => $height]);
    }

    public function applyCSS(StyleSheet $styles)
    {
        $this->styles = $styles;
    }

    public function generateLayout(Layout $layout, $dstFileName)
    {
        $this->layout = $layout;
        $this->bgColor = $this->styles->rules['Application']['backgroundColor'];

        foreach ($layout->getWindows() as $child) {
            /** @var Window $child */
            $child->createTitleBar();
            $this->addChild($child);
        }

        $this->generatePng($dstFileName);
    }

    protected function generatePng($filename)
    {
        $canvas = imagecreatetruecolor($this->absW, $this->absH);
        imagefill($canvas, 0, 0, self::color($canvas, $this->bgColor));

        $this->canvas = $canvas;
        $this->render($canvas);

        imagepng($canvas, $filename);
    }

    public function getWindows()
    {
        foreach ($this->children as $child) {
            yield $child;
        }
    }
}