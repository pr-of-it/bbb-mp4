<?php

namespace ProfIT\Bbb\Layout;

use Runn\Core\Std;

class TextRow extends Box
{
    const FONT_PATH = __DIR__ . '/../../resources/fonts/arial.ttf';

    const TEXT_LEFT_OFFSET = 5;

    protected $text;
    protected $bold;

    public function __construct(Std $props, string $text, int $size = null, string $color = null, bool $bold = false)
    {
        parent::__construct($props);

        $this->fontSize = $size;
        $this->h = $this->fontSize * 1.5;

        $this->text = $text;
        $this->fontColor = $color ?? Box::COLOR_BLACK;
        $this->bold = $bold;
    }

    public function render($canvas)
    {
        parent::render($canvas);

        self::renderText($canvas, $this->text, $this->bold);
    }

    public function alignRight()
    {
        $bbox = imagettfbbox($this->fontSize, 0, static::FONT_PATH, $this->text);
        $textWidth = $bbox[2] - $bbox[0];

        $this->x = $this->x + $this->w - $this->pad - $textWidth - 5;
    }
}
