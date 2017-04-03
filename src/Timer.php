<?php

namespace ProfIT\Bbb;

/**
 * Class Timer
 * @package ProfIT\Bbb
 */
class Timer
{
    /** @var  \DateTime */
    protected $start;
    /** @var  \DateInterval */
    protected $total;

    public function run()
    {
        $this->start = new \DateTime();
        $this->total = null;
    }

    public function lock()
    {
        $lockTime = new \DateTime();
        $this->total = $this->start->diff($lockTime);
    }

    public function getTotalTime(string $format)
    {
        return $this->total->format($format);
    }
}