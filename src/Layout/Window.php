<?php

namespace ProfIT\Bbb\Layout;

class Window
    extends Box
{
    public $name;

    const TITLES = [
        'PresentationWindow' => 'Презентация',
        'VideoDock'          => 'Веб-камера',
        'ChatWindow'         => 'Чат',
        'UsersWindow'        => 'Пользователи',
        'Deskshare'          => 'Трансляция рабочего стола',
    ];

    public function __construct(StyleSheet $styles, array $props = [])
    {
        parent::__construct($styles, $props);

        $this->bgColor = $this->styles->rules['.videoViewStyleNoFocus']['backgroundColor'];
        $this->bdColor = $this->styles->rules['.videoViewStyleNoFocus']['borderColor'];
    }

    public function createTitleBar()
    {
        $titleBar = new TitleBar($this->styles, array_merge($this->getContentCoordinates(), ['pad' => $this->pad]));

        $this->addChild($titleBar);
        $this->addOffset('top', $titleBar->h + $this->pad);
    }

    public function createContent()
    {
        $content = new Content($this->styles, $this->getContentCoordinates());

        $this->addChild($content);
    }

    public function getCoordinates()
    {
        return array_merge(
            [$this->name],
            [$this->x, $this->y, $this->w, $this->h],
            array_values($this->getContentCoordinates())
        );
    }

    public function getContentCoordinates()
    {
        return [
            'x' => $this->x + $this->pad + $this->offset['left'],
            'y' => $this->y + $this->pad + $this->offset['top'],
            'w' => $this->w - $this->pad * 2 - $this->offset['left'] - $this->offset['right'],
            'h' => $this->h - $this->pad * 2 - $this->offset['top'] - $this->offset['bottom'],
        ];
    }
    
    public function addText(string $text) {
        $textRow = new Text($this->styles, array_merge($this->getContentCoordinates(), ['pad' => 5]), $text);

        $this->addChild($textRow);
        $this->addOffset('top', $textRow->h + $this->pad);
    }
}