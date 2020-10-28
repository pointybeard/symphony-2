<?php

namespace Symphony\Symphony\Installer;

use Symphony\Symphony;

/**
 * @package content
 */

class Page extends Symphony\AbstractHtmlPage
{
    private $template;

    protected $params;

    protected $pageTitle;

    public function __construct($template, $params = [])
    {
        parent::__construct();

        $this->template = $template;
        $this->params = $params;

        $this->pageTitle = __('Install Symphony');
    }

    public function generate($page = null)
    {
        $this->Html->setDTD('<!DOCTYPE html>');
        $this->Html->setAttribute('lang', Symphony\Lang::get());

        $this->addHeaderToPage('Cache-Control', 'no-cache, must-revalidate, max-age=0');
        $this->addHeaderToPage('Expires', 'Mon, 12 Dec 1982 06:14:00 GMT');
        $this->addHeaderToPage('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        $this->addHeaderToPage('Pragma', 'no-cache');

        $this->setTitle($this->pageTitle);
        $this->addElementToHead(new Symphony\XmlElement('meta', null, array('charset' => 'UTF-8')), 1);
        $this->addElementToHead(new Symphony\XmlElement('meta', null, array('name' => 'robots', 'content' => 'noindex')), 2);

        $this->addStylesheetToHead(APPLICATION_URL . '/assets/css/installer.min.css', 'screen', 30);

        return parent::generate($page);
    }

    protected function __build($version = VERSION, Symphony\XmlElement $extra = null): void
    {
        parent::__build();

        $this->Form = Symphony\Widget::Form(INSTALL_URL . '/index.php', 'post');

        $title = new Symphony\XmlElement('h1', $this->pageTitle);
        $version = new Symphony\XmlElement('em', __('Version %s', array($version)));

        $title->appendChild($version);

        if (!is_null($extra)) {
            $title->appendChild($extra);
        }

        $this->Form->appendChild($title);

        if (isset($this->params['show-languages']) && $this->params['show-languages']) {
            $languages = new Symphony\XmlElement('ul');

            foreach (Symphony\Lang::getAvailableLanguages(false) as $code => $lang) {
                $languages->appendChild(new Symphony\XmlElement(
                    'li',
                    Symphony\Widget::Anchor(
                        $lang,
                        '?lang=' . $code
                    ),
                    ($_REQUEST['lang'] == $code || ($_REQUEST['lang'] == null && $code == 'en')) ? array('class' => 'selected') : []
                ));
            }

            $languages->appendChild(new Symphony\XmlElement(
                'li',
                Symphony\Widget::Anchor(
                    __('Symphony is also available in other languages'),
                    'http://getsymphony.com/download/extensions/translations/'
                ),
                array('class' => 'more')
            ));

            $this->Form->appendChild($languages);
        }

        $this->Body->appendChild($this->Form);

        $function = 'view' . str_replace('-', '', ucfirst($this->template));
        $this->$function();
    }

    protected function viewMissinglog()
    {
        $h2 = new Symphony\XmlElement('h2', __('Missing log file'));

        // What folder wasn't writable? The docroot or the logs folder?
        // RE: #1706
        if (is_writeable(DOCROOT) === false) {
            $folder = DOCROOT;
        } elseif (is_writeable(MANIFEST) === false) {
            $folder = MANIFEST;
        } elseif (is_writeable(INSTALL_LOGS) === false) {
            $folder = INSTALL_LOGS;
        }

        $p = new Symphony\XmlElement('p', __('Symphony tried to create a log file and failed. Make sure the %s folder is writable.', array('<code>' . $folder . '</code>')));

        $this->Form->appendChild($h2);
        $this->Form->appendChild($p);
        $this->setHttpStatus(Page::HTTP_STATUS_ERROR);
    }

    protected function viewRequirements()
    {
        $h2 = new Symphony\XmlElement('h2', __('System Requirements'));

        $this->Form->appendChild($h2);

        if (!empty($this->params['errors'])) {
            $div = new Symphony\XmlElement('div');
            $this->__appendError(array_keys($this->params['errors']), $div, __('Symphony needs the following requirements to be met before things can be taken to the “next level”.'));

            $this->Form->appendChild($div);
        }
        $this->setHttpStatus(Page::HTTP_STATUS_ERROR);
    }

    protected function viewLanguages()
    {
        $h2 = new Symphony\XmlElement('h2', __('Language selection'));
        $p = new Symphony\XmlElement('p', __('This installation can speak in different languages. Which one are you fluent in?'));

        $this->Form->appendChild($h2);
        $this->Form->appendChild($p);

        $languages = [];

        foreach (Symphony\Lang::getAvailableLanguages(false) as $code => $lang) {
            $languages[] = array($code, ($code === 'en'), $lang);
        }

        if (count($languages) > 1) {
            $languages[0][1] = false;
            $languages[1][1] = true;
        }

        $this->Form->appendChild(Symphony\Widget::Select('lang', $languages));

        $Submit = new Symphony\XmlElement('div', null, array('class' => 'submit'));
        $Submit->appendChild(Symphony\Widget::Input('action[proceed]', __('Proceed with installation'), 'submit'));

        $this->Form->appendChild($Submit);
    }

    protected function viewFailure()
    {
        $h2 = new Symphony\XmlElement('h2', __('Installation Failure'));
        $p = new Symphony\XmlElement('p', __('An error occurred during installation.'));

        // Attempt to get log information from the log file
        try {
            $log = file_get_contents(INSTALL_LOGS . '/install');
        } catch (\Exception $ex) {
            $log_entry = Symphony\Symphony::Log()->popFromLog();
            if (isset($log_entry['message'])) {
                $log = $log_entry['message'];
            } else {
                $log = 'Unknown error occurred when reading the install log';
            }
        }

        $code = new Symphony\XmlElement('code', $log);

        $this->Form->appendChild($h2);
        $this->Form->appendChild($p);
        $this->Form->appendChild(
            new Symphony\XmlElement('pre', $code)
        );
        $this->setHttpStatus(Page::HTTP_STATUS_ERROR);
    }

    protected function viewSuccess()
    {
        $symphonyUrl = URL . '/' . Symphony\Symphony::Configuration()->get('admin-path', 'symphony');
        $this->Form->setAttribute('action', $symphonyUrl);

        $div = new Symphony\XmlElement('div');
        $div->appendChild(
            new Symphony\XmlElement('h2', __('The floor is yours'))
        );
        $div->appendChild(
            new Symphony\XmlElement('p', __('Thanks for taking the quick, yet epic installation journey with us. It’s now your turn to shine!'))
        );
        $this->Form->appendChild($div);

        $ul = new Symphony\XmlElement('ul');
        foreach ($this->params['disabled-extensions'] as $handle) {
            $ul->appendChild(
                new Symphony\XmlElement('li', '<code>' . $handle . '</code>')
            );
        }

        if ($ul->getNumberOfChildren() !== 0) {
            $this->Form->appendChild(
                new Symphony\XmlElement('p',
                    __('Looks like the following extensions couldn’t be enabled and must be manually installed. It’s a minor setback in our otherwise prosperous future together.')
                )
            );
            $this->Form->appendChild($ul);
        }

        $this->Form->appendChild(
            new Symphony\XmlElement('p',
                __('I think you and I will achieve great things together.')
            )
        );

        $submit = new Symphony\XmlElement('div', null, array('class' => 'submit'));
        $submit->appendChild(Symphony\Widget::Input('submit', __('Okay, now take me to the login page'), 'submit'));

        $this->Form->appendChild($submit);
    }

    protected function viewConfiguration()
    {
    /* -----------------------------------------------
     * Populating fields array
     * -----------------------------------------------
     */

        $fields = isset($_POST['fields']) ? $_POST['fields'] : $this->params['default-config'];

    /* -----------------------------------------------
     * Welcome
     * -----------------------------------------------
     */
        $div = new Symphony\XmlElement('div');
        $div->appendChild(
            new Symphony\XmlElement('h2', __('Find something sturdy to hold on to because things are about to get awesome.'))
        );
        $div->appendChild(
            new Symphony\XmlElement('p', __('Think of this as a pre-game warm up. You know you’re going to kick-ass, so you’re savouring every moment before the show. Welcome to the Symphony install page.'))
        );

        $this->Form->appendChild($div);

        if (!empty($this->params['errors'])) {
            $this->Form->appendChild(
                Symphony\Widget::Error(new Symphony\XmlElement('p'), __('Oops, a minor hurdle on your path to glory! There appears to be something wrong with the details entered below.'))
            );
        }

    /* -----------------------------------------------
     * Environment settings
     * -----------------------------------------------
     */

        $fieldset = new Symphony\XmlElement('fieldset');
        $div = new Symphony\XmlElement('div');
        $this->__appendError(array('no-write-permission-root', 'no-write-permission-workspace'), $div);
        if ($div->getNumberOfChildren() > 0) {
            $fieldset->appendChild($div);
            $this->Form->appendChild($fieldset);
        }

    /* -----------------------------------------------
     * Website & Locale settings
     * -----------------------------------------------
     */

        $Environment = new Symphony\XmlElement('fieldset');
        $Environment->appendChild(new Symphony\XmlElement('legend', __('Website Preferences')));

        $label = Symphony\Widget::Label(__('Name'), Symphony\Widget::Input('fields[general][sitename]', $fields['general']['sitename']));

        $this->__appendError(array('general-no-sitename'), $label);
        $Environment->appendChild($label);

        $label = Symphony\Widget::Label(__('Admin Path'), Symphony\Widget::Input('fields[symphony][admin-path]', $fields['symphony']['admin-path']));

        $this->__appendError(array('no-symphony-path'), $label);
        $Environment->appendChild($label);

        $Fieldset = new Symphony\XmlElement('fieldset', null, array('class' => 'frame'));
        $Fieldset->appendChild(new Symphony\XmlElement('legend', __('Date and Time')));
        $Fieldset->appendChild(new Symphony\XmlElement('p', __('Customise how Date and Time values are displayed throughout the Administration interface.')));

        // Timezones
        $options = Symphony\DateTimeObj::getTimezonesSelectOptions((
            isset($fields['region']['timezone']) && !empty($fields['region']['timezone'])
                ? $fields['region']['timezone']
                : date_default_timezone_get()
        ));
        $Fieldset->appendChild(Symphony\Widget::Label(__('Region'), Symphony\Widget::Select('fields[region][timezone]', $options)));

        // Date formats
        $options = Symphony\DateTimeObj::getDateFormatsSelectOptions($fields['region']['date_format']);
        $Fieldset->appendChild(Symphony\Widget::Label(__('Date Format'), Symphony\Widget::Select('fields[region][date_format]', $options)));

        // Time formats
        $options = Symphony\DateTimeObj::getTimeFormatsSelectOptions($fields['region']['time_format']);
        $Fieldset->appendChild(Symphony\Widget::Label(__('Time Format'), Symphony\Widget::Select('fields[region][time_format]', $options)));

        $Environment->appendChild($Fieldset);
        $this->Form->appendChild($Environment);

    /* -----------------------------------------------
     * Database settings
     * -----------------------------------------------
     */

        $Database = new Symphony\XmlElement('fieldset');
        $Database->appendChild(new Symphony\XmlElement('legend', __('Database Connection')));
        $Database->appendChild(new Symphony\XmlElement('p', __('Please provide Symphony with access to a database.')));

        // Database name
        $label = Symphony\Widget::Label(__('Database'), Symphony\Widget::Input('fields[database][db]', $fields['database']['db']));

        $this->__appendError(array('database-incorrect-version', 'unknown-database'), $label);
        $Database->appendChild($label);

        // Database credentials
        $Div = new Symphony\XmlElement('div', null, array('class' => 'two columns'));
        $Div->appendChild(Symphony\Widget::Label(__('Username'), Symphony\Widget::Input('fields[database][user]', $fields['database']['user']), 'column'));
        $Div->appendChild(Symphony\Widget::Label(__('Password'), Symphony\Widget::Input('fields[database][password]', $fields['database']['password'], 'password'), 'column'));

        $this->__appendError(array('database-invalid-credentials'), $Div);
        $Database->appendChild($Div);

        // Advanced configuration
        $Fieldset = new Symphony\XmlElement('fieldset', null, array('class' => 'frame'));
        $Fieldset->appendChild(new Symphony\XmlElement('legend', __('Advanced Configuration')));
        $Fieldset->appendChild(new Symphony\XmlElement('p', __('Leave these fields unless you are sure they need to be changed.')));

        // Advanced configuration: Host, Port
        $Div = new Symphony\XmlElement('div', null, array('class' => 'two columns'));
        $Div->appendChild(Symphony\Widget::Label(__('Host'), Symphony\Widget::Input('fields[database][host]', $fields['database']['host']), 'column'));
        $Div->appendChild(Symphony\Widget::Label(__('Port'), Symphony\Widget::Input('fields[database][port]', $fields['database']['port']), 'column'));

        $this->__appendError(array('no-database-connection'), $Div);
        $Fieldset->appendChild($Div);

        // Advanced configuration: Table Prefix
        $label = Symphony\Widget::Label(__('Table Prefix'), Symphony\Widget::Input('fields[database][tbl_prefix]', $fields['database']['tbl_prefix']));

        $this->__appendError(array('database-table-prefix'), $label);
        $Fieldset->appendChild($label);

        $Database->appendChild($Fieldset);
        $this->Form->appendChild($Database);

    /* -----------------------------------------------
     * Permission settings
     * -----------------------------------------------
     */

        $Permissions = new Symphony\XmlElement('fieldset');
        $Permissions->appendChild(new Symphony\XmlElement('legend', __('Permission Settings')));
        $Permissions->appendChild(new Symphony\XmlElement('p', __('Set the permissions Symphony uses when saving files/directories.')));

        $Div = new Symphony\XmlElement('div', null, array('class' => 'two columns'));
        $Div->appendChild(Symphony\Widget::Label(__('Files'), Symphony\Widget::Input('fields[file][write_mode]', $fields['file']['write_mode']), 'column'));
        $Div->appendChild(Symphony\Widget::Label(__('Directories'), Symphony\Widget::Input('fields[directory][write_mode]', $fields['directory']['write_mode']), 'column'));

        $Permissions->appendChild($Div);
        $this->Form->appendChild($Permissions);

    /* -----------------------------------------------
     * User settings
     * -----------------------------------------------
     */

        $User = new Symphony\XmlElement('fieldset');
        $User->appendChild(new Symphony\XmlElement('legend', __('User Information')));
        $User->appendChild(new Symphony\XmlElement('p', __('Once installation is complete, you will be able to log in to the Symphony admin area with these user details.')));

        // Username
        $label = Symphony\Widget::Label(__('Username'), Symphony\Widget::Input('fields[user][username]', $fields['user']['username']));

        $this->__appendError(array('user-no-username'), $label);
        $User->appendChild($label);

        // Password
        $Div = new Symphony\XmlElement('div', null, array('class' => 'two columns'));
        $Div->appendChild(Symphony\Widget::Label(__('Password'), Symphony\Widget::Input('fields[user][password]', $fields['user']['password'], 'password'), 'column'));
        $Div->appendChild(Symphony\Widget::Label(__('Confirm Password'), Symphony\Widget::Input('fields[user][confirm-password]', $fields['user']['confirm-password'], 'password'), 'column'));

        $this->__appendError(array('user-no-password', 'user-password-mismatch'), $Div);
        $User->appendChild($Div);

        // Personal information
        $Fieldset = new Symphony\XmlElement('fieldset', null, array('class' => 'frame'));
        $Fieldset->appendChild(new Symphony\XmlElement('legend', __('Personal Information')));
        $Fieldset->appendChild(new Symphony\XmlElement('p', __('Please add the following personal details for this user.')));

        // Personal information: First Name, Last Name
        $Div = new Symphony\XmlElement('div', null, array('class' => 'two columns'));
        $Div->appendChild(Symphony\Widget::Label(__('First Name'), Symphony\Widget::Input('fields[user][firstname]', $fields['user']['firstname']), 'column'));
        $Div->appendChild(Symphony\Widget::Label(__('Last Name'), Symphony\Widget::Input('fields[user][lastname]', $fields['user']['lastname']), 'column'));

        $this->__appendError(array('user-no-name'), $Div);
        $Fieldset->appendChild($Div);

        // Personal information: Email Address
        $label = Symphony\Widget::Label(__('Email Address'), Symphony\Widget::Input('fields[user][email]', $fields['user']['email']));

        $this->__appendError(array('user-invalid-email'), $label);
        $Fieldset->appendChild($label);

        $User->appendChild($Fieldset);
        $this->Form->appendChild($User);

    /* -----------------------------------------------
     * Submit area
     * -----------------------------------------------
     */

        $this->Form->appendChild(new Symphony\XmlElement('h2', __('Install Symphony')));
        $this->Form->appendChild(new Symphony\XmlElement('p', __('The installation process goes by really quickly. Make sure to take a deep breath before you press that sweet button.', array('<code>' . basename(INSTALL_URL) . '</code>'))));

        $Submit = new Symphony\XmlElement('div', null, array('class' => 'submit'));
        $Submit->appendChild(Symphony\Widget::Input('lang', Symphony\Lang::get(), 'hidden'));

        $Submit->appendChild(Symphony\Widget::Input('action[install]', __('Install Symphony'), 'submit'));

        $this->Form->appendChild($Submit);

        if (isset($this->params['errors'])) {
            $this->setHttpStatus(Page::HTTP_STATUS_BAD_REQUEST);
        }
    }

    private function __appendError(array $codes, Symphony\XmlElement &$element, $message = null)
    {
        if (is_null($message)) {
            $message =  __('The following errors have been reported:');
        }

        foreach ($codes as $i => $c) {
            if (!isset($this->params['errors'][$c])) {
                unset($codes[$i]);
            }
        }

        if (!empty($codes)) {
            if (count($codes) > 1) {
                $ul = new Symphony\XmlElement('ul');

                foreach ($codes as $c) {
                    if (isset($this->params['errors'][$c])) {
                        $ul->appendChild(new Symphony\XmlElement('li', $this->params['errors'][$c]['details']));
                    }
                }

                $element = Symphony\Widget::Error($element, $message);
                $element->appendChild($ul);
            } else {
                $code = array_pop($codes);

                if (isset($this->params['errors'][$code])) {
                    $element = Symphony\Widget::Error($element, $this->params['errors'][$code]['details']);
                }
            }
        }
    }
}
