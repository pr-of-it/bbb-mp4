<?php

namespace ProfIT\Bbb\Layout;

class TitleBar
    extends Box
{
    const FONT_PATH = __DIR__ . '/../../resources/fonts';

    /** @var Window $parent */
    public $parent;
    public $relX = 0;
    public $relY = 0;
    public $relW = 1;
    public $h = 30;
    public $borderColor = 'dddddd';

    public function render($canvas)
    {
        parent::render($canvas);

        $text = $this->parent->title;
        $font = self::FONT_PATH. '/arial.ttf';
        $size = 12;

        list($llx, $lly, $lrx, $lry, $urx, $ury, $ulx, $uly) = imagettfbbox($size, 0, $font, $text);

        $textHeight = $lly - $uly;
        $offsetX = 2;
        $offsetY = floor(($this->h - $textHeight) / 2);

        $c = $this->getCoordinates();

        $x = $c[0][0] + $offsetX;
        $y = $c[0][1] + $textHeight + $offsetY;

        imagettftext($canvas, $size, 0, $x, $y, self::color($canvas, $this->color), $font, $text);

        $this->parent->addOffset(0, $this->absH);
    }
}