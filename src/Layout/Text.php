<?php

namespace ProfIT\Bbb\Layout;

use ProfIT\Bbb\Layout;

class Text
    extends Box
{
    const FONT_PATH = __DIR__ . '/../../resources/fonts/arial.ttf';
    
    protected $text;

    public function __construct(StyleSheet $styles, array $props = [], string $text)
    {
        parent::__construct($styles, $props);

        $this->bgColor   = self::COLOR_WHITE;
        $this->bdColor   = self::COLOR_WHITE;
        $this->fontColor = $this->styles->rules['.mdiWindowTitle']['color'];
        $this->fontSize  = $this->styles->rules['.mdiWindowTitle']['fontSize'];
        $this->h         = (int)$this->styles->rules['.videoViewStyleNoFocus']['headerHeight'];
        
        $this->text = $text;
    }

    public function render($canvas)
    {
        parent::render($canvas);

        $text = $this->text;

        $textHeight = $this->fontSize;
        $offsetY = floor(($this->h - $textHeight) / 2);

        $x = $this->x + $this->pad;
        $y = $this->y + $this->fontSize + $offsetY;

        imagettftext($canvas, $textHeight, 0, $x, $y, self::color($canvas, $this->fontColor), self::FONT_PATH, $text);
    }
}