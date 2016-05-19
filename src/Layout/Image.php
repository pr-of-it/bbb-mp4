<?php

namespace ProfIT\Bbb\Layout;

use ProfIT\Bbb\Layout;

class Image
    extends Box
{
    public $x = 0;
    public $y = 0;

    protected $layout;

    public function __construct(int $width, int $height)
    {
        parent::__construct(null, ['w' => $width, 'h' => $height]);
    }

    public function applyCSS(StyleSheet $styles)
    {
        $this->styles = $styles;
    }

    public function generateLayout(Layout $layout, $dstFileName, bool $fillContent)
    {
        $this->layout = $layout;
        $this->bgColor = $this->styles->rules['Application']['backgroundColor'];

        foreach ($layout->getWindows() as $child) {
            /** @var Window $child */
            $child->createTitleBar();
            if (true === $fillContent) {
                $child->createContent();
            }
            $this->addChild($child);
        }

        $this->generatePng($dstFileName);
    }

    public function generateWindowPng($params, $dstFileName)
    {
        $canvas = imagecreatetruecolor($this->absW, $this->absH);
        imagealphablending($canvas, false);
        $transparency = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparency);
        imagesavealpha($canvas, true);


        $window = new Window($this->styles, [
            'name' => $params[0],
            'x'    => 0,
            'y'    => 0,
            'w'    => $params[3],
            'h'    => $params[4],
            'pad'  => $params[5],
        ]);

        $window->createTitleBar();
        $this->addChild($window);

        $windowCanvas = imagecreatetruecolor($params[3], $params[4]);
        $window->render($windowCanvas);

        imagecopy($canvas, $windowCanvas, $params[1], $params[2], 0, 0, $params[3], $params[4]);
        imagepng($canvas, $dstFileName);
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