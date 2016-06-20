<?php

namespace ProfIT\Bbb\Layout;

use ProfIT\Bbb\Layout;

class TextRow
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
        $this->fontSize  = $this->styles->rules['.mdiWindowTitle']['fontSize'] * 0.75;
        $this->h         = $this->styles->rules['.mdiWindowTitle']['fontSize'] * 1.5;
        
        $this->text = $text;
    }

    public function render($canvas)
    {
        parent::render($canvas);

        self::renderText($canvas, $this->text);
    }
}