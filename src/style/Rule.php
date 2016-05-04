<?php

namespace ProfIT\Bbb\Style;

class Rule
{
    public $selector;
    public $properties;

    public function __construct($selector, $declaration)
    {
        $this->selector = trim($selector);
        $declaration = trim($declaration);

        foreach (explode(';', $declaration) as $row) {
            if ('' === trim($row)) continue;
            list ($name, $value) = explode(':', $row);

            $name = trim($name);
            if (!$name) continue;

            $this->properties[$name] = trim($value);
        }
    }
}