<?php

namespace ProfIT\Bbb\Layout;

use ProfIT\Bbb\Layout;

class TitleBar
    extends Box
{
    const FONT_PATH = __DIR__ . '/../../resources/fonts/arial.ttf';

    public function __construct(StyleSheet $styles, array $props = [])
    {
        parent::__construct($styles, $props);

        $this->bgColor   = $this->styles->rules['.videoViewStyleNoFocus']['backgroundColor'];
        $this->bdColor   = $this->styles->rules['.videoViewStyleNoFocus']['backgroundColor'];
        $this->fontColor = $this->styles->rules['.mdiWindowTitle']['color'];
        $this->fontSize  = $this->styles->rules['.mdiWindowTitle']['fontSize'];
        $this->h         = (int)$this->styles->rules['.videoViewStyleNoFocus']['headerHeight'];
    }

    public function render($canvas)
    {
        parent::render($canvas);

        $text = Layout::WINDOW_TITLES[$this->parent->name];

        $textHeight = $this->fontSize;
        $offsetY = floor(($this->h - $textHeight) / 2);

        $x = $this->x + $this->pad;
        $y = $this->y + $this->fontSize + $offsetY;

        imagettftext($canvas, $textHeight, 0, $x, $y, self::color($canvas, $this->fontColor), self::FONT_PATH, $text);
    }
}