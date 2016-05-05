<?php

namespace ProfIT\Bbb;

class StyleSheet
{
    public $rules = [];

    public function __construct($filename)
    {
        $this->loadRulesFromFile($filename);
    }

    public function loadRulesFromFile($filename)
    {
        $this->rules = [];

        $content = @file_get_contents($filename);
        if (false === $content) {
            echo 'Error while reading stylesheets file' . PHP_EOL;
            exit(0);
        }
        $content = preg_replace('~\/\*.*?\*\/~ms', '', $content);

        preg_match_all('~([^{}]+)\s*\{(.*?)\}~ms', $content, $m, PREG_SET_ORDER);
        if (0 === count($m)) {
            echo 'Stylesheets file empty' . PHP_EOL;
            exit(0);
        }

        foreach ($m as $data) {
            $selector = trim($data[1]);
            $declaration = trim($data[2]);

            foreach (explode(';', $declaration) as $row) {
                if ('' === trim($row)) continue;
                list ($name, $value) = explode(':', $row);

                $name = trim($name);
                if (!$name) continue;

                $this->rules[$selector][$name] = trim($value);
            }
        }
    }

}