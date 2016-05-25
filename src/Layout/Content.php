<?php

namespace ProfIT\Bbb\Layout;

use ProfIT\Bbb\Layout;

class Content
    extends Box
{
    public function __construct(StyleSheet $styles, array $props = [])
    {
        parent::__construct($styles, $props);

        $this->bgColor  = self::COLOR_WHITE;
        $this->bdColor  = self::COLOR_WHITE;
    }
}