<?php

namespace ProfIT\Bbb;

use ProfIT\Bbb\Layout\Layout;
use Running\Core\Collection;
use Running\Core\Std;

/**
 * Class TFunctions
 * @package ProfIT\Bbb
 *
 * @mixin Process
 */
trait TImageFunctions
{
    /**
     * Generate images in PNG format for every slide of PDF file
     *
     * @param array $sources
     * @throws \Exception
     */
    protected function generatePresentationImages(array $sources) {
        $dstPath = $this->dst . 'slides';
        if (!file_exists($dstPath)) {
            mkdir($dstPath);
        }

        $window = $this->layout->getWindowByName('PresentationWindow');
        foreach ($sources as $pdf) {
            if (!is_readable($pdf)) {
                $this->error('File ' . $pdf . ' does not exist or is not readable');
            }

            $dst = $dstPath . '/' . basename($pdf, '.pdf');
            if (!file_exists($dst)) {
                mkdir($dst);
            }

            $command = 'convert -density 150 -scene 1 ' . $pdf .
                ' -resize ' . ($window->w - $window->pad * 2) . 'x' . ($window->h - $window->pad * 2) .
                ' ' . realpath($dst) . '/slide.png';
            exec($command);
        }
    }

    /**
     * Generate image based on coordinates and list of texts
     *
     * @param string $dst
     * @param Std $coords
     * @param array $list
     */
    protected function generateListImage(string $dst, Std $coords, array $list)
    {
        if (!file_exists(dirname($dst))) {
            mkdir(dirname($dst));
        }

        $layout = new Layout($this->config->paths->resources . '/layout.xml', 'defaultlayout', $this->styles);
        $layout->setDimensions($coords->w, $coords->h, 0);
        $layout->addListWindow(['x' => 0, 'y' => 0, 'w' => 1, 'h' => 1], $list);
        $layout->generatePng($dst, true, false);
    }

    /**
     * Generate image based on coordinates and list of chat messages
     *
     * @param string $dst
     * @param Std $coords
     * @param Collection $messages
     */
    protected function generateChatListImage(string $dst, Std $coords, Collection $messages)
    {
        if (!file_exists(dirname($dst))) {
            mkdir(dirname($dst));
        }

        $layout = new Layout($this->config->paths->resources . '/layout.xml', 'defaultlayout', $this->styles);
        $layout->setDimensions($coords->w, $coords->h, 0);
        $layout->addChatListWindow(['x' => 0, 'y' => 0, 'w' => 1, 'h' => 1], $messages, $this->events);
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
    protected function addImageToFilters(&$filters, $start, $end, $coordX, $coordY, $num)
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
     * @return Std
     */
    protected function getVideoPictureDimensions(string $fileName)
    {
        exec('ffprobe -v quiet -i ' . $fileName . ' -show_entries stream=width,height -of csv=p=0', $output);
        $dimensions = explode(',', $output[0]);

        return new Std([
            'w'  => (int)$dimensions[0],
            'h' => (int)$dimensions[1],
        ]);
    }

    /**
     * Get video resized dimensions
     *
     * @param string $fileName
     * @param int $width
     * @param int $height
     *
     * @return Std
     */
    protected function getVideoResizedDimensions(string $fileName, int $width, int $height)
    {
        $videoPictureDimensions = $this->getVideoPictureDimensions($fileName);
        $videoWidth = $videoPictureDimensions->w;
        $videoHeight = $videoPictureDimensions->h;
        $resized = false;

        while ($videoWidth > $width || $videoHeight > $height) {
            $resized = true;
            $videoWidth = round($videoWidth * 0.9);
            $videoHeight = round($videoHeight * 0.9);
        }

        return new Std([
            'w'       => $videoWidth,
            'h'       => $videoHeight,
            'resized' => $resized,
        ]);
    }

    protected function makeDeskshareLayout(string $src, string $dst, int $pad)
    {
        if (!file_exists(dirname($dst))) {
            mkdir(dirname($dst));
        }

        $titleHeight = (int)$this->styles->rules['.videoViewStyleNoFocus']['headerHeight'];
        $contentWidth = $this->width - 2 * $pad;
        $contentHeight = $this->height - $titleHeight - 3 * $pad;

        $video = $this->getVideoResizedDimensions($src, $contentWidth, $contentHeight);

        $params = new Std([
            'w'      => $video->w + 2 * $pad,
            'h'      => $video->h + $titleHeight + 3 * $pad,
            'x'      => round(($this->width - ($video->w + 2 * $pad)) / 2),
            'y'      => round(($this->height - ($video->h + $titleHeight + 3 * $pad)) / 2),
        ]);

        $layout = new Layout($this->config->paths->resources . '/layout.xml', 'defaultlayout', $this->styles);
        $layout->setDimensions($params->w, $params->h, $pad);
        $layout->addCustomWindow([
            'name' => 'Deskshare',
            'x' => 0,
            'y' => 0,
            'w' => 1,
            'h' => 1,
        ]);
        $layout->setMarkedWindows(['Deskshare']);
        $layout->generatePng($dst, false, true);

        $window = $layout->getWindowByName('Deskshare');

        $coords = $window->getCoordinates();
        $coords->x = $params->x;
        $coords->y = $params->y;
        $coords->cx += $params->x;
        $coords->cy += $params->y;

        return [$coords, $video->resized];
    }
}