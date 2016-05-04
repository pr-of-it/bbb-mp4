<?php

namespace ProfIT\Bbb\Layout;

/**
 * Class Box
 * @package ProfIT\Bbb\layout
 * @property-read int $absX calculated absolute x
 * @property-read int $absY calculated absolute y
 * @property-read int $absW calculated absolute width
 * @property-read int $absH calculated absolute height
 */

class Box {
    const COLOR_BLACK = '000000';
    const COLOR_GRAY  = 'cccccc';
    const COLOR_WHITE = 'ffffff';

    public $x;
    public $y;
    public $w;
    public $h;

    public $relX;
    public $relY;
    public $relW;
    public $relH;

    public $minW;
    public $minH;

    /** @var int absolute padding */
    public $pad = 0;
    /** @var array absolute positive content offset [top, right, bottom, left] */
    public $offset = [0, 0, 0, 0];

    /** @var Box */
    public $parent;
    public $children = [];
    public $hidden;

    public $color = self::COLOR_BLACK;
    public $bgColor;
    public $borderColor;

    public function __construct(array $props = [])
    {
        foreach ($props as $key => $val) {
            if (null !== $val) {
                $this->$key = $val;
            }
        }

        if ($pad = $this->pad) {
            $this->setPadding($pad);
        }
    }


    public function getCoordinates()
    {
        $x = $this->absX;
        $y = $this->absY;

        return [[$x, $y], [$x + $this->absW, $y + $this->absH]];
    }

    public function render($canvas)
    {
        if ($this->hidden) return;

        if ($this->parent) {
            $c = $this->getCoordinates();

            if ($bgColor = $this->bgColor) {
                imagefilledrectangle($canvas, $c[0][0], $c[0][1], $c[1][0], $c[1][1], self::color($canvas, $bgColor));
            }

            if ($borderColor = $this->borderColor) {
                imagerectangle($canvas, $c[0][0], $c[0][1], $c[1][0], $c[1][1], self::color($canvas, $borderColor));
            }
        }

        foreach ($this->children as $child) {
            /** @var Window $child */
            $child->render($canvas);
        }
    }

    public function addChild(Box $child)
    {
        $child->parent = $this;

        $this->children[] = $child;
    }

    public function __get(string $name)
    {
        if (strpos($name, 'abs') === 0) {
            $prop = substr($name, 3);

            if (! in_array($prop, ['X', 'Y', 'W', 'H'])) {
                throw new \Exception("Property $name is not defined");
            }

            if (($direct = $this->{strtolower($prop)}) !== null) return $direct;

            return $this->{'getAbs'. $prop}($this->parent);
        }

        throw new \Exception("Property $name is not defined");
    }

    public function getAbsX(Box $parent)
    {
        $offset = $this->relX * $parent->absW;

        return round($parent->absX + $offset + $parent->offset[3]);
    }

    public function getAbsY(Box $parent)
    {
        $offset = $this->relY * $parent->absH + $parent->offset[0];

        return (int) round($parent->absY + $offset);
    }

    public function getAbsW(Box $parent)
    {
        $os = $parent->offset;
        $w = $this->relW * $parent->absW - $os[1] - $os[3];

        //if ($w < $this->minW) $w = $this->minW;

        return (int) round($w);
    }

    public function getAbsH(Box $parent)
    {
        $os = $parent->offset;
        $h = $this->relH * $parent->absH - $os[0] - $os[2];

        //if ($h < $this->minH) $h = $this->minH;

        return (int) round($h);
    }

    public function setPadding(int $value)
    {
        $os = &$this->offset;

        $os[0] += $value;
        $os[1] += $value;
        $os[2] += $value;
        $os[3] += $value;
    }

    public function addOffset($what, $value = null)
    {
        if (is_array($what)) {
            $what = array_filter($what, 'is_numeric');

            foreach ($what as $i => $value) {
                if (! isset($this->offset[$i])) throw new \Exception('Unknown offset: '. $i);

                $this->offset[$i] += $value;
            }

            return $this->offset;
        }

        $this->offset[$what] += $value;

        return $this->offset;
    }

    public static function color($canvas, string $value)
    {
        list ($r, $g, $b) = array_map('hexdec', str_split($value, 2));

        return imagecolorallocate($canvas, $r, $g, $b);
    }
}