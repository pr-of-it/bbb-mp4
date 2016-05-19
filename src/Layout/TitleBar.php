<?php

namespace ProfIT\Bbb\Layout;

use ProfIT\Bbb\Layout;

class TitleBar
    extends Box
{
    const FONT_PATH = __DIR__ . '/../../resources/fonts/arial.ttf';

    /** @var Window $parent */
    public $parent;
    public $relX = 0;
    public $relY = 0;
    public $relW = 1;

    public function __construct(StyleSheet $styles, array $props = [])
    {
        parent::__construct($styles, $props);

        $this->bgColor  = $this->styles->rules['.videoViewStyleNoFocus']['backgroundColor'];
        $this->bdColor  = $this->styles->rules['.videoViewStyleNoFocus']['backgroundColor'];
        $this->color    = $this->styles->rules['.mdiWindowTitle']['color'];
        $this->fontSize = $this->styles->rules['.mdiWindowTitle']['fontSize'];
        $this->h        = $this->styles->rules['.videoViewStyleNoFocus']['headerHeight'];
    }

    public function render($canvas)
    {
        parent::render($canvas);

        $text = Layout::WINDOWS[$this->parent->name];

        $textHeight = $this->fontSize;
        $offsetY = floor(($this->h - $textHeight) / 2);

        $c = $this->getCoordinates();

        $x = $c[0][0] + $this->pad;
        $y = $c[0][1] + $textHeight + $offsetY;

        imagettftext($canvas, $this->fontSize, 0, $x, $y, self::color($canvas, $this->color), self::FONT_PATH, $text);

        $this->parent->addOffset(0, $this->absH + $this->pad);
    }
}