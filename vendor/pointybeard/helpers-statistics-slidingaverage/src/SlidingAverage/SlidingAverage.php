<?php

namespace pointybeard\Helpers\Statistics\SlidingAverage;

// Credit to Alexey Volynskov for the C# implementation of a Ring Buffer
// https://gamedevelopment.tutsplus.com/tutorials/quick-tip-use-the-ring-buffer-data-structure-to-smooth-jittery-values--gamedev-14373
// Ported to PHP by Alannah Kearney
class SlidingAverage
{
    private $buffer;
    private $size;
    private $sum;
    private $lastIndex;

    public function __construct($size, $initialValue)
    {
        if (!is_numeric($size) || (int)$size <= 0) {
            throw new Exceptions\InvalidBufferSizeException("Size must be a positive integer.");
        }

        $this->lastIndex = 0;
        $this->size = $size;
        $this->reset($initialValue);
    }

    public function reset($value)
    {
        $this->sum = $value * $this->size;
        $this->buffer = array_fill(0, $this->size, $value);
    }

    public function push($value)
    {
        // subtract the oldest sample from the sum
        $this->sum -= $this->buffer[$this->lastIndex];

        // add the new sample
        $this->sum += $value;

        // store the new sample
        $this->buffer[$this->lastIndex] = $value;

        // advance the index
        $this->lastIndex++;

        // wrap index around to 0 if necessary
        if ($this->lastIndex >= $this->size) {
            $this->lastIndex = 0;
        }
    }

    public function sample()
    {
        return $this->sum * (1 / $this->size);
    }
}
