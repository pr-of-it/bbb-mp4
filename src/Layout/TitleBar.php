<?php

namespace ProfIT\Bbb\Layout;

use Running\Core\Std;

class TitleBar
    extends Box
{
    const FONT_PATH = __DIR__ . '/../../resources/fonts/arial.ttf';

    public function __construct(StyleSheet $styles, Std $props = null)
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
        
        self::renderText($canvas, Window::TITLES[$this->parent->name] ?? $this->parent->name);
    }
}