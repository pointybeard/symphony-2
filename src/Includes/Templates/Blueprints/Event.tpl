<?php

//declare(strict_types=1);

use Symphony\Symphony\Events;

final class event<!-- CLASS NAME --> extends Events\<!-- CLASS EXTENDS -->
{
    public $ROOTELEMENT = '<!-- ROOT ELEMENT -->';

    public $eParamFILTERS = [
        <!-- FILTERS -->
    ];

    public static function about(): array
    {
        return [
            'name' => '<!-- NAME -->',
            'author' => [
                'name' => '<!-- AUTHOR NAME -->',
                'website' => '<!-- AUTHOR WEBSITE -->',
                'email' => '<!-- AUTHOR EMAIL -->'
            ],
            'version' => '<!-- VERSION -->',
            'release-date' => '<!-- RELEASE DATE -->',
            'trigger-condition' => 'action[<!-- TRIGGER CONDITION -->]'
        ];
    }

    public static function getSource()
    {
        return '<!-- SOURCE -->';
    }

    public static function allowEditorToParse(): bool
    {
        return true;
    }

    public static function documentation()
    {
        return \PHP_EOL . '<!-- DOCUMENTATION -->';
    }

    public function load(): ?\XMLElement
    {
        if (true == isset($_POST['action']['<!-- TRIGGER CONDITION -->'])) {
            return $this->__trigger();
        }

        return null;
    }

}
