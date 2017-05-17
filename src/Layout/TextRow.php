<?php

namespace ProfIT\Bbb\Layout;

use Runn\Core\Std;

class TextRow
    extends Box
{
    const FONT_PATH = __DIR__ . '/../../resources/fonts/arial.ttf';

    const TEXT_LEFT_OFFSET = 5;
    
    protected $text;
    protected $bold;

    public function __construct(StyleSheet $styles, Std $props, string $text, string $color = null, bool $bold = false)
    {
        parent::__construct($styles, $props);

        $this->bgColor   = self::COLOR_WHITE;
        $this->bdColor   = self::COLOR_WHITE;
        $this->fontColor = $color ?? $this->styles->rules['.mdiWindowTitle']['color'];
        $this->fontSize  = $this->styles->rules['.mdiWindowTitle']['fontSize'] * 0.75;
        $this->h         = $this->styles->rules['.mdiWindowTitle']['fontSize'] * 1.5;

        $this->text = $text;
        $this->bold = $bold;
    }

    public function render($canvas)
    {
        parent::render($canvas);

        self::renderText($canvas, $this->text, $this->bold);
    }

    public function cutTextToWidth()
    {
        $maxTextWidth = $this->w - 2 * $this->pad;

        $bbox = imagettfbbox($this->fontSize, 0, static::FONT_PATH, $this->text);
        $textWidth = $bbox[2] - $bbox[0];

        if ($textWidth <= $maxTextWidth) {
            return false;
        }

        $words = explode(' ', $this->text);

        foreach (range(1, count($words)) as $numWordsToCut) {
            $wordsCutted = array_slice($words, 0, count($words) - $numWordsToCut);
            $textCutted = implode(' ', $wordsCutted);
            $bbox = imagettfbbox($this->fontSize, 0, static::FONT_PATH, $textCutted);
            $textWidth = $bbox[2] - $bbox[0];

            if ($textWidth <= $maxTextWidth) {
                $this->text = $textCutted;
                return implode(' ', array_slice($words, count($words) - $numWordsToCut));
            }
        }

        return false;
    }

    public function alignRight()
    {
        $bbox = imagettfbbox($this->fontSize, 0, static::FONT_PATH, $this->text);
        $textWidth = $bbox[2] - $bbox[0];

        $this->x = $this->x + $this->w - $this->pad - $textWidth - 5;
    }
}