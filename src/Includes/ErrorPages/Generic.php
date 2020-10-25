<?php

//declare(strict_types=1);

use Symphony\Symphony;

$output = new Symphony\HtmlPage;

$output->Html->setElementStyle('html');

$output->Html->setDTD('<!DOCTYPE html>');

$output->Html->setAttribute('lang', 'en');

$output->addElementToHead(
    new Symphony\XmlElement(
        'meta',
        null,
        ['http-equiv' => 'Content-Type', 'content' => 'text/html; charset=UTF-8']
    ),
    0
);

$output->addStylesheetToHead(ASSETS_URL.'/css/symphony.min.css', 'screen', null, false);

$output->setHttpStatus($e->getHttpStatusCode());
$output->addHeaderToPage('Content-Type', 'text/html; charset=UTF-8');
$output->addHeaderToPage('Symphony-Error-Type', 'generic');

if (isset($e->getAdditional()->header)) {
    $output->addHeaderToPage($e->getAdditional()->header);
}

$output->setTitle(__('%1$s &ndash; %2$s', [__('Symphony'), $e->getHeading()]));
$output->Body->setAttribute('id', 'error');

$div = new Symphony\XmlElement('div', null, ['class' => 'frame']);

$div->appendChild(new Symphony\XmlElement('h1', $e->getHeading()));

$div->appendChild(
    ($e->getMessageObject() instanceof Symphony\\XmlElement 
        ? $e->getMessageObject() 
        : new Symphony\XmlElement('p', trim($e->getMessage()))
    )
);

$output->Body->appendChild($div);

echo $output->generate();

exit(1);
