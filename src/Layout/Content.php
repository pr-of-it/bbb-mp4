<?php

namespace ProfIT\Bbb\Layout;

use ProfIT\Bbb\Layout;

class Content
    extends Box
{
    const FONT_PATH = __DIR__ . '/../../resources/fonts/arial.ttf';

    /** @var Window $parent */
    public $parent;
    public $relX = 0;
    public $relY = 0;
    public $relW = 1;
    public $relH = 1;

    public function __construct(StyleSheet $styles, array $props = [])
    {
        parent::__construct($styles, $props);

        $this->bgColor  = self::COLOR_WHITE;
    }
}