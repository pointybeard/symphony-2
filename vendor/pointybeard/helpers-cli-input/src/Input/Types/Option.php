<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input\Types;

use pointybeard\Helpers\Functions\Strings;
use pointybeard\Helpers\Functions\Cli;
use pointybeard\Helpers\Cli\Input;

class Option extends Input\AbstractInputType
{
    public function getDisplayName(): string
    {
        return '-'.$this->name();
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

        $first = Strings\mb_str_pad(
            $this->getDisplayName(),
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
