<?php

/**
 * Generate image based on coordinates and list of texts
 *
 * @param string $dst
 * @param array $coords
 * @param array $list
 *
 * @return array
 */
function generateListImage(string $dst, array $coords, array $list)
{
    if (!file_exists(dirname($dst))) {
        mkdir(dirname($dst));
    }
    $css = new \ProfIT\Bbb\Layout\StyleSheet(__DIR__ . '/resources/style/css/BBBDefault.css');

    $layout = new \ProfIT\Bbb\Layout(__DIR__ . '/resources/layout.xml', 'defaultlayout', $css);
    $layout->setDimensions($coords['w'], $coords['h'], 0);
    $layout->addListWindow(['x' => 0, 'y' => 0, 'w' => 1, 'h' => 1, 'pad' => 5], $list);
    $layout->generatePng($dst, true, false);
}

/**
 * Generate image based on coordinates and list of chat messages
 *
 * @param string $dst
 * @param array $coords
 * @param array $list
 *
 * @return array
 */
function generateChatListImage(string $dst, array $coords, array $list, \ProfIT\Bbb\EventsFile $events)
{
    if (!file_exists(dirname($dst))) {
        mkdir(dirname($dst));
    }
    $css = new \ProfIT\Bbb\Layout\StyleSheet(__DIR__ . '/resources/style/css/BBBDefault.css');

    $layout = new \ProfIT\Bbb\Layout(__DIR__ . '/resources/layout.xml', 'defaultlayout', $css);
    $layout->setDimensions($coords['w'], $coords['h'], 0);
    $layout->addChatListWindow(['x' => 0, 'y' => 0, 'w' => 1, 'h' => 1, 'pad' => 5], $list, $events);
    $layout->generatePng($dst, true, false);
}
