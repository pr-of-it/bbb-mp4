<?php

namespace ProfIT\Bbb\Layout;

use Running\Core\Std;

/**
 * Class Box
 * @package ProfIT\Bbb\Layout
 */

class Box
{
    const COLOR_BLACK       = '#000000';
    const COLOR_GRAY        = '#CCCCCC';
    const COLOR_WHITE       = '#FFFFFF';
    const DEFAULT_FONT_SIZE = 12;

    /** @var int coordinates and sizes */
    public $x;
    public $y;
    public $w;
    public $h;
    
    public $yCorrection;

    /** @var int padding */
    public $pad = 0;

    /** @var array content offset */
    public $offset = [
        'top'    => 0,
        'right'  => 0,
        'bottom' => 0,
        'left'   => 0
    ];

    /** @var Box|Window */
    public $parent;
    /** @var array */
    public $children = [];

    /** @var bool */
    public $hidden;

    /** @var StyleSheet */
    public $styles;

    /** default styles */
    protected $bgColor   = self::COLOR_GRAY;
    protected $bdColor   = self::COLOR_BLACK;
    protected $fontColor = self::COLOR_BLACK;
    protected $fontSize  = self::DEFAULT_FONT_SIZE;

    public function __construct(StyleSheet $styles = null, Std $props = null)
    {
        $this->styles = $styles;

        foreach ($props as $key => $val) {
            if (null !== $val) {
                $this->$key = $val;
            }
        }
    }

    public function render($canvas)
    {
        if ($this->hidden) {
            return;
        }

        $yCorrection = $this->parent->yCorrection ?? 0;
        imagefilledrectangle(
            $canvas,
            $this->x,
            $this->y + $yCorrection,
            $this->x + $this->w - 1,
            $this->y + $yCorrection + $this->h - 1,
            self::color($canvas, $this->bgColor)
        );
        imagerectangle(
            $canvas,
            $this->x,
            $this->y + $yCorrection,
            $this->x + $this->w - 1,
            $this->y + $yCorrection + $this->h - 1,
            self::color($canvas, $this->bdColor)
        );

        foreach ($this->children as $child) {
            /** @var Box $child */
            $child->render($canvas);
        }
    }
    
    public function renderText($canvas, string $text, bool $bold = false)
    {
        $textHeight = $this->fontSize;
        $offsetY = floor(($this->h - $textHeight) / 2);

        $x = $this->x + $this->pad;
        $yCorrection = $this->parent->yCorrection ?? 0;
        $y = $this->y + $yCorrection + $this->fontSize + $offsetY;

        imagettftext(
            $canvas,
            $textHeight,
            0,
            $x,
            $y,
            self::color($canvas, $this->fontColor),
            static::FONT_PATH,
            $text
        );
        if (true === $bold) {
            imagettftext(
                $canvas,
                $textHeight,
                0,
                $x+1,
                $y+1,
                self::color($canvas, $this->fontColor),
                static::FONT_PATH,
                $text
            );
        }
    }

    public function addChild(Box $child)
    {
        $child->parent = $this;
        $this->children[] = $child;
    }

    public function addOffset($side, $value)
    {
        $this->offset[$side] += $value;
    }

    public static function color($canvas, string $value)
    {
        list ($r, $g, $b) = array_map('hexdec', str_split(trim($value, "#"), 2));

        return imagecolorallocate($canvas, $r, $g, $b);
    }
}