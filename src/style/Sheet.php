<?php

namespace ProfIT\Bbb\Style;

class Sheet
{
    public $rules = [];

    public function __construct($filename)
    {
        $this->loadRulesFromFile($filename);
    }

    public function loadRulesFromFile($filename)
    {
        $content = file_get_contents($filename);
        $content = preg_replace('~\/\*.*?\*\/~ms', '', $content);

        preg_match_all('~([^{}]+)\s*\{(.*?)\}~ms', $content, $m, PREG_SET_ORDER);

        foreach ($m as $data) {
            $rule = new Rule($data[1], $data[2]);

            $this->rules[] = $rule;
        }
    }
}