<?php

//declare(strict_types=1);

use Symphony\Symphony;

$match = '';
$rename_failed = false;

// Fetch extensions
if (is_dir(EXTENSIONS)) {
    $extensions = new DirectoryIterator(EXTENSIONS);
    // Look for folders that could be the same as the desired extension
    foreach ($extensions as $extension) {
        if ($extension->isDot() || $extension->isFile()) {
            continue;
        }

        // See if we can find an extension in any of the folders that has the id we are looking for in `extension.meta.xml`
        if (@file_exists($extension->getPathname().'/extension.meta.xml')) {
            $xsl = @file_get_contents($extension->getPathname().'/extension.meta.xml');
            $xsl = @new SimpleXMLElement($xsl);
            if (!$xsl) {
                continue;
            }
            $xsl->registerXPathNamespace('ext', 'http://getsymphony.com/schemas/extension/1.0');
            $result = $xsl->xpath("//ext:extension[@id = '".$e->getAdditional()->name."']");

            if (!empty($result)) {
                $match = $extension->getFilename();
                break;
            }
        }
    }
}

// The extension cannot be found, show an error message and
// let the user remove or rename the extension folder.
if (isset($_POST['extension-missing'])) {
    $redirect = false;
    if (isset($_POST['action']['delete'])) {
        \Symphony::ExtensionManager()->cleanupDatabase();
        $redirect = true;
    } elseif (isset($_POST['action']['rename']) && '' != $match) {
        $path = ExtensionManager::__getDriverPath($match);

        if (!@rename(EXTENSIONS.'/'.$match, EXTENSIONS.'/'.$e->getAdditional()->name)) {
            $rename_failed = true;
        } else {
            $redirect = true;
        }
    }
    if ($redirect) {
        redirect(SYMPHONY_URL.'/system/extensions/');
    }
}

$output = new Symphony\HtmlPage;

$output->Html->setElementStyle('html');

$output->Html->setDTD('<!DOCTYPE html>');
$output->Html->setAttribute('lang', 'en');
$output->addElementToHead(new Symphony\XmlElement('meta', null, array('http-equiv' => 'Content-Type', 'content' => 'text/html; charset=UTF-8')), 0);
$output->addStylesheetToHead(ASSETS_URL.'/css/symphony.min.css', 'screen', null, false);

$output->setHttpStatus($e->getHttpStatusCode());
$output->addHeaderToPage('Content-Type', 'text/html; charset=UTF-8');
$output->addHeaderToPage('Symphony-Error-Type', 'missing-extension');

$output->setTitle(__('%1$s &ndash; %2$s', array(__('Symphony'), $e->getHeading())));
$output->Body->setAttribute('id', 'error');

$div = new Symphony\XmlElement('div', null, array('class' => 'frame'));
$div->appendChild(new Symphony\XmlElement('h1', $e->getHeading()));
$div->appendChild(
    new Symphony\XmlElement('p', trim($e->getMessage()))
);

// Build the form, what it can do is yet to be determined
$form = new Symphony\XmlElement('form', null, array('action' => SYMPHONY_URL.'/system/extensions/', 'method' => 'post'));
$form->appendChild(
    Symphony\Widget::Input('extension-missing', 'yes', 'hidden')
);
$actions = new Symphony\XmlElement('div');
$actions->setAttribute('class', 'actions');

$actions->appendChild(Widget::Input('action[delete]', __('Uninstall extension'), 'submit', array(
    'accesskey' => 'd',
    'class' => 'button delete',
    'style' => 'margin-left: 0;',
    'title' => __('Uninstall this extension'),
)));

$form->appendChild($actions);

// if the renamed failed
if ('' != $match && $rename_failed) {
    $div->appendChild(
        new Symphony\XmlElement('p', __('Sorry, but Symphony was unable to rename the folder. You can try renaming %s to %s yourself, or you can uninstall the extension to continue.', array(
            '<code>extensions/'.Symphony\General::sanitize($match).'</code>',
            '<code>extensions/'.Symphony\General::sanitize($e->getAdditional()->name).'</code>',
        )))
    );
}
// If we've found a similar folder
elseif ('' != $match) {
    $div->appendChild(
        new Symphony\XmlElement('p', __('Often the cause of this error is a misnamed extension folder. You can try renaming %s to %s, or you can uninstall the extension to continue.', array(
            '<code>extensions/'.$match.'</code>',
            '<code>extensions/'.$e->getAdditional()->name.'</code>',
        )))
    );

    $button = new Symphony\XmlElement('button', __('Rename folder'));
    $button->setAttributeArray(array(
        'name' => 'action[rename]',
        'class' => 'button',
        'type' => 'submit',
        'accesskey' => 's',
    ));
    $actions->appendChild($button);
} else {
    $div->appendChild(
        new Symphony\XmlElement('p', __('You can try uninstalling the extension to continue, or you might want to ask on the forums'))
    );
}

// Add XSRF token to form's in the backend
if (\Symphony::Engine()->isXSRFEnabled()) {
    $form->prependChild(Symphony\Xsrf::formToken());
}

$div->appendChild($form);

$output->Body->appendChild($div);

echo $output->generate();

exit(1);
