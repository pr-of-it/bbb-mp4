<?php

namespace ProfIT\Bbb\Layout;

class Window
    extends Box
{
    public $name;
    protected $titleBar;

    public function __construct(StyleSheet $styles, array $props = [])
    {
        parent::__construct($styles, $props);

        $this->bgColor = $this->styles->rules['.videoViewStyleNoFocus']['backgroundColor'];
        $this->bdColor = $this->styles->rules['.videoViewStyleNoFocus']['borderColor'];
    }

    public function createTitleBar()
    {
        $titleBar = new TitleBar($this->styles, ['pad' => $this->pad]);

        $this->addChild($titleBar);
    }

    public function createContent()
    {
        $content = new Content($this->styles);

        $this->addChild($content);
    }

    public function getContentCoordinates()
    {
        return [
            $this->name,
            $this->absX,
            $this->absY,
            $this->absW,
            $this->absH,
            $this->absX + $this->offset[3],
            $this->absY + $this->offset[0],
            $this->absW - $this->offset[1] - $this->offset[3],
            $this->absH - $this->offset[0] - $this->offset[2],
        ];
    }
}