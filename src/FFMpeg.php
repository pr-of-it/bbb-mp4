<?php

namespace ProfIT\Bbb;

use Runn\Core\Collection;
use Runn\Core\Std;

/**
 * Class FFMpeg
 * @package ProfIT\Bbb
 */
class FFMpeg
{
    /** @var Collection $sources */
    public $sources;
    /** @var Collection $filters */
    public $filters;

    public function __construct()
    {
        $this->sources = new Collection();
        $this->filters = new Collection();
    }

    public function addImageFilter(float $start, float $end, int $x, int $y)
    {
        $prefixPart = (0 === $this->filters->count() ? '[1:v]' : '[out]') . '[' . ($this->sources->count() - 1) . ':v]';
        $overlayPart = 'overlay=' . $x . ':' . $y . ':enable=\'between(t,' . $start . ',' . $end . ')\'';
        $filterString = $prefixPart . ' ' . $overlayPart . ' [out]';

        $this->filters->append($filterString);
    }

    public function addVideoFilter(Std $video, Std $coords)
    {
        $filterScale = '[' . ($this->sources->count() - 1) . ':v] scale=' .
            $coords->w . ':' . $coords->h .' [' . ($this->sources->count() - 1) . 's]';
        $this->filters->append($filterScale);

        $filterOverflowTrim = '[' . ($this->sources->count() - 1) . 's] ' .
            'trim=duration=' . ($video->end - $video->start) . ' [' . ($this->sources->count() - 1) . 't]';
        $this->filters->append($filterOverflowTrim);

        $filterOverflow = '[out]' . '[' . ($this->sources->count() - 1) . 't]' .
            ' overlay=' . $coords->x . ':' . $coords->y . ':enable=\'between(t,' .
            $video->start . ',' . $video->end . ')\' [out]';
        $this->filters->append($filterOverflow);
    }

    public function addSoundSource($sound)
    {
        $this->sources->append('-i ' . $sound);
    }

    public function addImageSource($image)
    {
        $this->sources->append('-i ' . $image);
    }

    public function addLoopImageSource($image)
    {
        $this->sources->append('-loop 1 -i ' . $image);
    }

    public function addVideoSource($video)
    {
        $this->sources->append('-itsoffset ' . $video->start . ' -i ' . $video->source);
    }

    public function export(string $dst, bool $showStats = false, string $filename = 'video')
    {
        $sourcesFile = $dst . 'sources.txt';
        $filtersFile = $dst . 'filters.txt';

        file_put_contents($sourcesFile, implode(' ' . PHP_EOL, $this->sources->toArray()));
        file_put_contents($filtersFile, implode(';' . PHP_EOL, $this->filters->toArray()));

        exec('ffmpeg -v quiet' . ($showStats ? ' -stats' : '') . ' -y ' . implode(' ', $this->sources->toArray()) .
            ' -filter_complex_script "' . $filtersFile .
            '" -map "[out]" -map 0:0 -c:v libx264 -preset ultrafast -pix_fmt yuv420p -c:a copy ' .
            '-shortest ' . $dst . $filename . '.avi');
    }
}