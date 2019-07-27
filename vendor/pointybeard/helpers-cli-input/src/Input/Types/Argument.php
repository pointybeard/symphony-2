<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input\Types;

use pointybeard\Helpers\Cli\Input;
use pointybeard\Helpers\Functions\Strings;
use pointybeard\Helpers\Functions\Cli;

class Argument extends Input\AbstractInputType
{
    public function __construct(string $name = null, int $flags = null, string $description = null, object $validator = null, $default = null)
    {
        if (null === $validator) {
            $validator = function (Input\AbstractInputType $input, Input\AbstractInputHandler $context) {
                // This dummy validator is necessary otherwise the argument
                // value is ALWAYS set to default (most often NULL) regardless
                // of if the argument was set or not
                return $context->find($input->name());
            };
        }

        parent::__construct($name, $flags, $description, $validator, $default);
    }

    public function getDisplayName(): string
    {
        return strtoupper($this->name());
    }

    public function __toString(): string
    {
        // MAGIC VALUES!!! OH MY.....
        $padCharacter = ' ';
        $paddingBufferSize = 0.15; // 15%
        $argumentNamePaddedWidth = 20;
        $argumentNameMinimumPaddingWidth = 4;
        $minimumWindowWidth = 80;

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
            $argumentNamePaddedWidth,
            $padCharacter,
            STR_PAD_LEFT
        );

        $first = Strings\mb_str_pad(
            $this->getDisplayName().str_repeat($padCharacter, $argumentNameMinimumPaddingWidth),
            $argumentNamePaddedWidth,
            $padCharacter
        );

        $second = Strings\utf8_wordwrap_array(
            $this->description(),
            $window['cols'] - $argumentNamePaddedWidth - $paddingBuffer
        );

        // Skip the first item (notice $ii starts at value of '1')
        for ($ii = 1; $ii < count($second); ++$ii) {
            $second[$ii] = $secondaryLineLeadPadding.$second[$ii];
        }

        return $first.implode($second, PHP_EOL);
    }
}
