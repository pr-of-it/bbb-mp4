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

    public function createTextRows(string $text, $pad = 0, string $color = null, bool $bold = false)
    {
        $cutText = $this->cutTextToWidth($text);
        $textContentHeight = 0;
        $textRows = [];
        foreach ($cutText as $row) {
            $textRow = new TextRow(
                $this->getContentCoordinates()->merge(new Std(['pad' => $pad])),
                $row,
                $this->fontSize,
                $color,
                $bold
            );
            $textRows[] = $textRow;
            $this->addChild($textRow);
            $textContentHeight = $textRow->h + $this->pad;
            $this->addOffset('top', $textContentHeight);
        }

        if ($this->offset['top'] > $this->h) {
            $this->yCorrection -= $textContentHeight * count($textRows);
            $this->h += $textContentHeight * count($textRows);
        }
        return $textRows;
    }

    /**
     * Cutting text to output window width
     *
     * @param $text
     * @return string[]
     */
    public function cutTextToWidth($text)
    {
        $maxTextWidth = $this->w - 2 * $this->pad - TextRow::TEXT_LEFT_OFFSET;
        $rows = [];

        while ($this->stringWidth($text) > $maxTextWidth) {
            $words = explode(' ', $text);
            if ($this->stringWidth($words[0]) > $maxTextWidth) {
                $longWord = array_shift($words);
                $words = array_merge($this->cutWordToWidth($longWord, $maxTextWidth), $words);
            }

            foreach (range(0, count($words)) as $numWordsToCut) {
                $textCutted = implode(' ', array_slice($words, 0, count($words) - $numWordsToCut));

                if ($this->stringWidth($textCutted) <= $maxTextWidth) {
                    $text = implode(' ', array_slice($words, count($words) - $numWordsToCut));
                    $rows[] = $textCutted;
                    break;
                }
            }
        }

        $rows[] = $text;
        return $rows;
    }

    /**
     * @param $str
     * @return int
     */
    protected function stringWidth($str)
    {
        $box = imagettfbbox($this->fontSize, 0, TextRow::FONT_PATH, $str);
        return $box[2] - $box[0];
    }

    /**
     * @param string $word
     * @param int $maxWidth
     * @return string[]
     */
    protected function cutWordToWidth($word, $maxWidth)
    {
        $parts = [];
        while ($this->stringWidth($word) > $maxWidth) {
            $chars = str_split($word);
            foreach (range(0, count($chars)) as $numCharsToCut) {
                $wordCutted = implode('', array_slice($chars, 0, count($chars) - $numCharsToCut));

                if ($this->stringWidth($wordCutted) <= $maxWidth) {
                    $word = implode('', array_slice($chars, count($chars) - $numCharsToCut));
                    $parts[] = $wordCutted;
                    break;
                }
            }
        }
        $parts[] = $word;
        return $parts;
    }

    public function createUserListRow(string $userName)
    {
        $this->createTextRows($userName, 2 * TextRow::TEXT_LEFT_OFFSET);
    }

    public function createChatListCaption(string $meetingName)
    {
        $this->createTextRows('Добро пожаловать в ' . $meetingName, TextRow::TEXT_LEFT_OFFSET, '#1672ba', true);
        $this->createTextRows('');
    }

    public function createChatMessageCaption(string $user, string $time)
    {
        $textRow = $this->createTextRows(
            $user,
            TextRow::TEXT_LEFT_OFFSET,
            self::COLOR_BLACK
        );
        $this->offset['top'] -= array_pop($textRow)->h;
        $textRow = $this->createTextRows(
            $time,
            TextRow::TEXT_LEFT_OFFSET,
            self::COLOR_BLACK
        );
        array_pop($textRow)->alignRight();
    }

    public function createChatMessage(string $text)
    {
        $this->createTextRows($text, 2 * TextRow::TEXT_LEFT_OFFSET);
    }
}
