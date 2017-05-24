<?php

namespace ProfIT\Bbb\Layout;

use Runn\Core\Std;

class Window extends Box
{
    public $name;

    public function getCoordinates()
    {
        $coordinates = new Std([
            'x' => $this->x,
            'y' => $this->y,
            'w' => $this->w,
            'h' => $this->h,
        ]);
        return $coordinates;
    }

    public function getContentCoordinates()
    {
        return new Std([
            'x' => $this->x + $this->pad + $this->offset['left'],
            'y' => $this->y + $this->pad + $this->offset['top'],
            'w' => $this->w - $this->pad * 2 - $this->offset['left'] - $this->offset['right'],
            'h' => $this->h - $this->pad * 2 - $this->offset['top'] - $this->offset['bottom'],
        ]);
    }

    public function createTextRow(string $text, $pad = 0, string $color = null, bool $bold = false)
    {
        $textRow = new TextRow($this->getContentCoordinates()->merge(new Std(['pad' => $pad])), $text, $color, $bold);
        $this->addChild($textRow);
        $textContentHeight = $textRow->h + $this->pad;
        $this->addOffset('top', $textContentHeight);

        $textOverflow = $textRow->cutTextToWidth();
        if (false !== $textOverflow) {
            $this->createTextRow($textOverflow, $pad, $color, $bold);
        }
        if ($this->offset['top'] > $this->h) {
            $this->yCorrection -= $textContentHeight;
            $this->h += $textContentHeight;
        }
        return $textRow;
    }

    public function createUserListRow(string $userName)
    {
        $this->createTextRow($userName, 2 * TextRow::TEXT_LEFT_OFFSET);
    }

    public function createChatListCaption(string $meetingName)
    {
        $this->createTextRow('Добро пожаловать в ' . $meetingName, TextRow::TEXT_LEFT_OFFSET, '#1672ba', true);
        $this->createTextRow('');
    }

    public function createChatMessageCaption(string $user, string $time)
    {
        $textRow = $this->createTextRow(
            $user,
            TextRow::TEXT_LEFT_OFFSET,
            self::COLOR_BLACK
        );
        $this->offset['top'] -= $textRow->h;
        $textRow = $this->createTextRow(
            $time,
            TextRow::TEXT_LEFT_OFFSET,
            self::COLOR_BLACK
        );
        $textRow->alignRight();
    }

    public function createChatMessage(string $text)
    {
        $this->createTextRow($text, 2 * TextRow::TEXT_LEFT_OFFSET);
    }
}
