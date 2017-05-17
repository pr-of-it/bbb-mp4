<?php

namespace ProfIT\Bbb\Layout;

use Runn\Core\Std;

class Content
    extends Box
{
    public function __construct(StyleSheet $styles, Std $props = null)
    {
        parent::__construct($styles, $props);

        $this->bgColor  = self::COLOR_WHITE;
        $this->bdColor  = self::COLOR_WHITE;
    }
}