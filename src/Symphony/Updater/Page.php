<?php

namespace Symphony\Symphony\Updater;

use Symphony\Symphony;

/**
 * @package content
 */

final class Page extends Symphony\Installer\Page
{
    public function __construct($template, $params = [])
    {
        parent::__construct($template, $params);

        $this->template = $template;
        $this->pageTitle = __('Update Symphony');
    }

    protected function __build($version = VERSION, ?Symphony\XmlElement $extra = null): void
    {
        parent::__build(
            // Replace the installed version with the updated version
            isset($this->_params['version'])
                ? $this->_params['version']
                : Symphony\Symphony::Configuration()->get('version', 'symphony')
        );

        // Add Release Notes for the latest migration
        if (isset($this->_params['release-notes'])) {
            $nodeset = $this->Form->getChildrenByName('h1');
            $h1 = end($nodeset);
            if ($h1 instanceof Symphony\XmlElement) {
                $h1->appendChild(
                    new Symphony\XmlElement(
                        'em',
                        Symphony\Widget::Anchor(__('Release Notes'), $this->_params['release-notes'])
                    )
                );
            }
        }
    }

    protected function viewUptodate()
    {
        $h2 = new Symphony\XmlElement('h2', __('Symphony is already up-to-date'));
        $p = new Symphony\XmlElement('p', __('It appears that Symphony has already been installed at this location and is up to date.'));

        $this->Form->appendChild($h2);
        $this->Form->appendChild($p);
    }

    protected function viewReady()
    {
        $h2 = new Symphony\XmlElement('h2', __('Updating Symphony'));
        $p = new Symphony\XmlElement('p', __('This script will update your existing Symphony installation to version %s.', array('<code>' . $this->_params['version'] . '</code>')));

        $this->Form->appendChild($h2);
        $this->Form->appendChild($p);

        if (!is_writable(CONFIG)) {
            $this->Form->appendChild(
                new Symphony\XmlElement('p', __('Please check that your configuration file is writable before proceeding'), array('class' => 'warning'))
            );
        }

        if (!empty($this->_params['pre-notes'])) {
            $h2 = new Symphony\XmlElement('h2', __('Pre-Installation Notes:'));
            $dl = new Symphony\XmlElement('dl');

            foreach ($this->_params['pre-notes'] as $version => $notes) {
                $dl->appendChild(new Symphony\XmlElement('dt', $version));
                foreach ($notes as $note) {
                    $dl->appendChild(new Symphony\XmlElement('dd', $note));
                }
            }

            $this->Form->appendChild($h2);
            $this->Form->appendChild($dl);
        }

        $submit = new Symphony\XmlElement('div', null, array('class' => 'submit'));
        $submit->appendChild(Symphony\Widget::input('action[update]', __('Update Symphony'), 'submit'));

        $this->Form->appendChild($submit);
    }

    protected function viewFailure()
    {
        $h2 = new Symphony\XmlElement('h2', __('Updating Failure'));
        $p = new Symphony\XmlElement('p', __('An error occurred while updating Symphony.'));

        // Attempt to get update information from the log file
        try {
            $log = file_get_contents(INSTALL_LOGS . '/update');
        } catch (\Exception $ex) {
            $log_entry = Symphony\Symphony::Log()->popFromLog();
            if (isset($log_entry['message'])) {
                $log = $log_entry['message'];
            } else {
                $log = 'Unknown error occurred when reading the update log';
            }
        }

        $code = new Symphony\XmlElement('code', $log);

        $this->Form->appendChild($h2);
        $this->Form->appendChild($p);
        $this->Form->appendChild(
            new Symphony\XmlElement('pre', $code)
        );
    }

    protected function viewSuccess()
    {
        $this->Form->setAttribute('action', SYMPHONY_URL);

        $h2 = new Symphony\XmlElement('h2', __('Updating Complete'));
        $this->Form->appendChild($h2);

        if (!empty($this->_params['post-notes'])) {
            $dl = new Symphony\XmlElement('dl');

            foreach ($this->_params['post-notes'] as $version => $notes) {
                if ($notes) {
                    $dl->appendChild(new Symphony\XmlElement('dt', $version));
                    foreach ($notes as $note) {
                        $dl->appendChild(new Symphony\XmlElement('dd', $note));
                    }
                }
            }

            $this->Form->appendChild($dl);
        }

        $this->Form->appendChild(
            new Symphony\XmlElement('p',
                __('And the crowd goes wild! A victory dance is in order; and look, your mum is watching. She\'s proud.', array(Symphony\Symphony::Configuration()->get('sitename', 'general')))
            )
        );
        $this->Form->appendChild(
            new Symphony\XmlElement('p',
                __('Your mum is also nagging you about %s before you log in.', array(
                        '<a href="' . URL . '/install/?action=remove">' .
                        __('removing that %s directory', array('<code>' . basename(INSTALL_URL) . '</code>')) .
                        '</a>'
                    )
                )
            )
        );

        $submit = new Symphony\XmlElement('div', null, array('class' => 'submit'));
        $submit->appendChild(Symphony\Widget::input('submit', __('Complete'), 'submit'));

        $this->Form->appendChild($submit);
    }
}
