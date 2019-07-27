<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input\Types;

use pointybeard\Helpers\Functions\Flags;
use pointybeard\Helpers\Functions\Strings;
use pointybeard\Helpers\Functions\Cli;
use pointybeard\Helpers\Cli\Input;

class LongOption extends Input\AbstractInputType
{
    protected $short;

    public function __construct(string $name = null, string $short = null, int $flags = null, string $description = null, object $validator = null, $default = false)
    {
        $this->short = $short;
        parent::__construct($name, $flags, $description, $validator, $default);
    }

    public function respondsTo(string $name): bool
    {
        return $name == $this->name || $name == $this->short;
    }

    public function getDisplayName(): string
    {
        $short =
            null !== $this->short()
            ? '-'.$this->short().', '
            : null
        ;

        return sprintf('%s--%s', $short, $this->name());
    }

    public function __toString(): string
    {
        // MAGIC VALUES!!! OH MY.....
        $padCharacter = ' ';
        $paddingBufferSize = 0.15; // 15%
        $optionNamePaddedWidth = 30;
        $minimumWindowWidth = 80;
        $secondaryLineIndentlength = 2;

        // Get the window dimensions but restrict width to minimum
        // of $minimumWindowWidth
        $window = Cli\get_window_size();
        $window['cols'] = max($minimumWindowWidth, $window['cols']);

        // This shrinks the total line length (derived by the window width) by
        // $paddingBufferSize
        $paddingBuffer = (int) ceil($window['cols'] * $paddingBufferSize);

        // Create a string of $padCharacter which is prepended to each secondary
        // line
        $secondaryLineLeadPadding = str_pad(
            '',
            $optionNamePaddedWidth,
            $padCharacter,
            STR_PAD_LEFT
        );

        $long = $this->getDisplayName();
        if (Flags\is_flag_set($this->flags(), self::FLAG_VALUE_REQUIRED)) {
            $long .= '=VALUE';
        } elseif (Flags\is_flag_set($this->flags(), self::FLAG_VALUE_OPTIONAL)) {
            $long .= '[=VALUE]';
        }

        $first = Strings\mb_str_pad(
            $long, // -O, --LONG,
            $optionNamePaddedWidth,
            $padCharacter
        );

        $second = Strings\utf8_wordwrap_array(
            $this->description(),
            $window['cols'] - $optionNamePaddedWidth - $paddingBuffer
        );

        for ($ii = 1; $ii < count($second); ++$ii) {
            $second[$ii] = $secondaryLineLeadPadding.$second[$ii];
        }

        return $first.implode($second, PHP_EOL);
    }
}
