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
        $layoutParams = [
            'name' => 'Layout',
            'x' => 0,
            'y' => 0,
            'w' => $this->w,
            'h' => $this->h,
        ];
        $deskshareParams = [
            'name' => 'Deskshare',
            'x' => (int) round(Layout::DESKSHARE['x'] * $this->w),
            'y' => (int) round(Layout::DESKSHARE['y'] * $this->h),
            'w' => (int) round(Layout::DESKSHARE['w'] * $this->w),
            'h' => (int) round(Layout::DESKSHARE['h'] * $this->h),
            'pad' => $this->layout->pad,
        ];

        $windows = [];
        $windows[] = $layoutParams;

        foreach ($this->children as $child) {
            /** @var \ProfIT\Bbb\Layout\Window $child */
            $windows[] = $child->getContentCoordinates();
        }

        $windows[] = $deskshareParams;

        return $windows;
    }
}