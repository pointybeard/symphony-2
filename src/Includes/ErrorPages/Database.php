<?php

use Symphony\Symphony;

$output = new Symphony\HtmlPage();

$output->Html->setElementStyle('html');

$output->Html->setDTD('<!DOCTYPE html>');
$output->Html->setAttribute('lang', 'en');
$output->addElementToHead(new Symphony\XmlElement('meta', null, array('http-equiv' => 'Content-Type', 'content' => 'text/html; charset=UTF-8')), 0);
$output->addStylesheetToHead(ASSETS_URL.'/css/symphony.min.css', 'screen', null, false);

$output->setHttpStatus($e->getHttpStatusCode());
$output->addHeaderToPage('Content-Type', 'text/html; charset=UTF-8');
$output->addHeaderToPage('Symphony-Error-Type', 'database');

if (isset($e->getAdditional()->header)) {
    $output->addHeaderToPage($e->getAdditional()->header);
}

$output->setTitle(__('%1$s &ndash; %2$s', array(__('Symphony'), __('Database Error'))));
$output->Body->setAttribute('id', 'error');

$div = new Symphony\XmlElement('div', null, array('class' => 'frame'));
$div->appendChild(new Symphony\XmlElement('h1', __('Symphony Database Error')));
$div->appendChild(new Symphony\XmlElement('p', $e->getAdditional()->message));
$div->appendChild(new Symphony\XmlElement('p', '<code>'.$e->getAdditional()->error->getDatabaseErrorCode().': '.$e->getAdditional()->error->getDatabaseErrorMessage().'</code>'));

$query = $e->getAdditional()->error->getQuery();

if (isset($query)) {
    $div->appendChild(new Symphony\XmlElement('p', '<code>'.$e->getAdditional()->error->getQuery().'</code>'));
}

$output->Body->appendChild($div);

echo $output->generate();
exit(1);
