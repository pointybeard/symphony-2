<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\ProgressBar;

use pointybeard\Helpers\Statistics\SlidingAverage;
use pointybeard\Helpers\Cli\Colour;
use pointybeard\Helpers\Functions\Time;
use pointybeard\Helpers\Functions\Cli;
use pointybeard\Helpers\Functions\Strings;

class ProgressBar
{
    private $start = null;
    private $length = 30;
    private $completed = 0;
    private $total = null;
    private $charProgress = '▓';
    private $charBlank = '░';
    private $foreground = Colour\Colour::FG_DEFAULT;
    private $background = Colour\Colour::BG_DEFAULT;
    private $format = '{{PROGRESS_BAR}} {{PERCENTAGE}}% {{COMPLETED}}/{{TOTAL}} ({{ELAPSED_TIME}} elapsed, approx. {{REMAINING_TIME}} remaining)';

    private $rateAverage = null;

    /**
     * Initialise the ProgressBar class by setting the total number of
     * work units it will be tracking.
     *
     * @param int $total
     */
    public function __construct($total)
    {
        $this->total($total);
    }

    /**
     * Magic method for getting and setting private variables. If $args is,
     * this method will instead return the value. When setting the value, an
     * instance of this class is returned, allowing method chaining.
     *
     * @param string $name the name of the class variable to get/set
     * @param mixed  $args value to assign to variable (optional)
     *
     * @return mixed Will return the value of the variable of $args
     *               has been omitted, otherwise an instance of $this
     */
    public function __call($name, $args)
    {
        if (empty($args)) {
            return $this->$name;
        }

        $this->$name = $args[0];

        return $this;
    }

    /**
     * Sets the start time which is used internally to calculate the elapsed and
     * remaining time.
     *
     * @param int $time value to seed $this->start with (optional)
     *
     * @return self instance of $this
     */
    public function start(int $time = null): self
    {
        $this->start = (
            null === $time
                ? time()
                : $time
        );

        return $this;
    }

    /**
     * Convenience method for determining of the start time has been set.
     *
     * @return bool true if it has been set
     */
    private function hasStarted(): bool
    {
        return null !== $this->start;
    }

    /**
     * Increase the work units completed by $units (Default is 1), then redraw
     * the progress bar.
     *
     * @param int $units a value to increase the progress bar by
     */
    public function advance(int $units = 1)
    {
        if (!$this->hasStarted()) {
            $this->start();
        }

        $this->completed = min($this->total, $this->completed + $units);
        $this->draw();
    }

    public function rate(): float
    {
        $instantaneousRate = $this->completed / (time() - $this->start);

        if (!($this->rateAverage instanceof SlidingAverage\SlidingAverage)) {
            $this->rateAverage = new SlidingAverage\SlidingAverage(
                floor($this->total * 0.10), // Total samples is 10%
                $instantaneousRate
            );
        } else {
            $this->rateAverage->push($instantaneousRate);
        }

        return $this->rateAverage->sample();
    }

    public function remaining(): int
    {
        return $this->total - $this->completed;
    }

    public function elapsedTime(): int
    {
        return time() - $this->start;
    }

    public function remainingTime(): int
    {
        return (int) round($this->remaining() / $this->rate());
    }

    public function percentageCompleted(): float
    {
        return (float) $this->completed / (float) $this->total;
    }

    private function buildProgressBar(): string
    {
        // This prevents broken characters
        $realLength = $this->length() * strlen($this->charBlank());

        $completedLength = floor(
            $this->percentageCompleted() * (
                $realLength * (1 / strlen($this->charProgress()))
            )
        );

        $result = str_repeat($this->charProgress(), (int) $completedLength);

        if ($completedLength < $realLength) {
            $result = str_pad(
                $result,
                $realLength,
                $this->charBlank()
            );
        } else {
            $result .= $this->charProgress();
        }

        return Colour\Colour::colourise(
            $result,
            $this->foreground(),
            $this->background()
        );
    }

    private function buildArgs(): array
    {
        return [
            'PROGRESS_BAR' => $this->buildProgressBar(),
            'PERCENTAGE' => number_format($this->percentageCompleted() * 100.0, 2),
            'COMPLETED' => $this->completed,
            'TOTAL' => $this->total,
            'ELAPSED_TIME' => Time\human_readable_time($this->elapsedTime(), true),
            'REMAINING_TIME' => (
                $this->elapsedTime() < 3
                    ? '?'
                    : Time\human_readable_time($this->remainingTime(), true)
            ),
        ];
    }

    private static function replacePlaceholdersInString(array $placeholders, array $replacements, string $input): string
    {
        $search = array_map(
            function ($value) {
                return "{{{$value}}}";
            },
            $placeholders
        );

        return str_replace($search, $replacements, $input);
    }

    public function draw(): void
    {
        $args = $this->buildArgs();

        echo Strings\mb_str_pad(sprintf(
            "\r%s",
            self::replacePlaceholdersInString(
                array_keys($args),
                array_values($args),
                $this->format
            )
        ), (Cli\get_window_size())['cols'] - 1, ' ', STR_PAD_RIGHT);
    }
}
