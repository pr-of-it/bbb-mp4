<?php

namespace ProfIT\Bbb\Layout;

class Window
    extends Box
{
    public $name;
    public $title;

    public function __construct(StyleSheet $styles, array $props = [])
    {
        parent::__construct($styles, $props);

        $this->bgColor = $this->styles->rules['.videoViewStyleNoFocus']['backgroundColor'];
        $this->bdColor = $this->styles->rules['.videoViewStyleNoFocus']['borderColor'];
    }

    public function createTitleBar()
    {
        $titleBar = new TitleBar($this->styles);

        $this->addChild($titleBar);
    }
}