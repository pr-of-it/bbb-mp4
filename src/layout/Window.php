<?php

namespace ProfIT\Bbb\Layout;

class Window
    extends Box
{
    public $name;
    public $title;
    public $bgColor = self::COLOR_GRAY;
    public $borderColor = self::COLOR_WHITE;

    public function createTitleBar()
    {
        $titleBar = new TitleBar();

        $this->addChild($titleBar);
    }
}