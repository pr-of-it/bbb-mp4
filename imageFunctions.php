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
    $layout->addListWindow(['x' => 0, 'y' => 0, 'w' => 1, 'h' => 1], $list);
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
    $layout->addChatListWindow(['x' => 0, 'y' => 0, 'w' => 1, 'h' => 1], $list, $events);
    $layout->generatePng($dst, true, false);
}

/**
 * Add ffmpeg filter element
 *
 * @param array $filters link to ffmpeg filter array
 * @param double $start image overlay start time
 * @param double $end image overlay end time
 * @param string $coordX image x coordinate
 * @param string $coordY image y coordinate
 * @param int $num number of filter element
 */
function addImageToFilters(&$filters, $start, $end, $coordX, $coordY, $num)
{
    $filters[] = (0 === count($filters) ? '[1:v]' : '[out]') . '[' . $num . ':v]' .
        ' overlay=' . $coordX . ':' . $coordY . ':enable=\'between(t,' .
        $start . ',' . $end . ')\' [out]';
}

/**
 * Get video picture dimensions
 *
 * @param string $fileName
 *
 * @return array 
 */
function getVideoPictureDimensions(string $fileName)
{
    exec('ffprobe -v quiet -i ' . $fileName . ' -show_entries stream=width,height -of csv=p=0', $output);
    $dimensions = explode(',', $output[0]);
    
    return [
        'width' => (int)$dimensions[0],
        'height' => (int)$dimensions[1],
    ];
}

/**
 * Get video resized dimensions
 *
 * @param string $fileName
 * @param int $width
 * @param int $height
 *
 * @return array
 */
function getVideoResizedDimensions(string $fileName, int $width, int $height)
{
    $videoPictureDimensions = getVideoPictureDimensions($fileName);
    $videoWidth = $videoPictureDimensions['width'];
    $videoHeight = $videoPictureDimensions['height'];
    $resize = false;

    while ($videoWidth > $width || $videoHeight > $height) {
        $resize = true;
        $videoWidth = round($videoWidth * 0.9);
        $videoHeight = round($videoHeight * 0.9);
    }

    return [
        'width' => $videoWidth,
        'height' => $videoHeight,
        'resize' => $resize,
    ];
}
