<?php

namespace ProfIT\Bbb\Events;

use Running\Core\Std;

/**
 * Class VoiceEvent
 * @package ProfIT\Bbb\Events
 *
 * @property double $time
 * @property string $file
 * @property int $slide
 */
class PresentationEvent
    extends Std
{
    public function getPath()
    {
        return basename($this->file, '.pdf') . '/slide-' . $this->slide . '.png';
    }
}