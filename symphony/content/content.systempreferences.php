<?php

/**
 * The Preferences page allows Developers to change settings for
 * this Symphony install. Extensions can extend the form on this
 * page so they can have their own settings. This page is typically
 * a UI for a subset of the `CONFIG` file.
 */
class contentSystemPreferences extends AdministrationPage
{
    public $_errors = [];

    // Overload the parent 'view' function since we dont need the switchboard logic
    public function view()
    {
        $this->setPageType('form');
        $this->setTitle(__('%1$s &ndash; %2$s', array(__('Preferences'), __('Symphony'))));
        $this->addElementToHead(new XMLElement('link', null, array(
            'rel' => 'canonical',
            'href' => SYMPHONY_URL.'/system/preferences/',
        )));
        $this->appendSubheading(__('Preferences'));

        $bIsWritable = true;
        $formHasErrors = (is_array($this->_errors) && !empty($this->_errors));

        if (false === General::checkFileWritable(CONFIG)) {
            $this->pageAlert(__('The Symphony configuration file, %s, or folder is not writable. You will not be able to save changes to preferences.', array('<code>/manifest/config.json</code>')), Alert::ERROR);
            $bIsWritable = false;
        } elseif ($formHasErrors) {
            $this->pageAlert(
                __('An error occurred while processing this form. See below for details.'),
                Alert::ERROR
            );
        } elseif (isset($this->_context[0]) && 'success' == $this->_context[0]) {
            $this->pageAlert(__('Preferences saved.'), Alert::SUCCESS);
        }

        // Get available languages
        $languages = Lang::getAvailableLanguages();

        if (count($languages) > 1) {
            // Create language selection
            $group = new XMLElement('fieldset');
            $group->setAttribute('class', 'settings');
            $group->appendChild(new XMLElement('legend', __('System Language')));
            $label = Widget::Label();

            // Get language names
            asort($languages);

            $options = [];
            foreach ($languages as $code => $name) {
                $options[] = array($code, $code == Symphony::Configuration()->get('lang', 'symphony'), $name);
            }

            $select = Widget::Select('settings[symphony][lang]', $options);
            $label->appendChild($select);
            $group->appendChild($label);
            $group->appendChild(new XMLElement('p', __('Authors can set up a differing language in their profiles.'), array('class' => 'help')));
            // Append language selection
            $this->Form->appendChild($group);
        }

        // Get available EmailGateways
        $emailGateways = EmailGatewayManager::listAll();

        if (count($emailGateways) >= 1) {
            $group = new XMLElement('fieldset', null, ['class' => 'settings condensed']);
            $group->appendChild(new XMLElement('legend', __('Default Email Settings')));
            $label = Widget::Label(__('Gateway'));

            // Get gateway names
            ksort($emailGateways);

            $defaultGateway = EmailGatewayManager::getDefaultGateway();
            $selectedIsInstalled = EmailGatewayManager::__getClassPath($defaultGateway);

            $options = [];

            foreach ($emailGateways as $handle => $details) {
                $options[] = [
                    $handle, 
                    (($handle == $defaultGateway) || ((false == $selectedIsInstalled) && 'Sendmail' == $handle)),
                    $details['name']
                ];
            }

            $select = Widget::Select(
                'settings[Email][default_gateway]',
                $options,
                ['class' => 'picker', 'data-interactive' => 'data-interactive']
            );

            $label->appendChild($select);
            $group->appendChild($label);
            // Append email gateway selection
            $this->Form->appendChild($group);
        }

        foreach ($emailGateways as $gateway) {
            $gatewaySettings = EmailGatewayManager::create($gateway['handle'])->getPreferencesPane();

            if (is_a($gatewaySettings, 'XMLElement')) {
                $this->Form->appendChild($gatewaySettings);
            }
        }

        // Get available cache drivers
        $caches = Symphony::ExtensionManager()->getProvidersOf('cache');
        // Add default Symphony cache driver..
        $caches['database'] = 'Database';

        if (count($caches) > 1) {
            $group = new XMLElement('fieldset', null, array('class' => 'settings condensed'));
            $group->appendChild(new XMLElement('legend', __('Default Cache Settings')));

            /*
             * Add custom Caching groups. For example a Datasource extension might want to add in the ability
             * for set a cache driver for it's functionality. This should usually be a dropdown, which allows
             * a developer to select what driver they want to use for caching. This choice is stored in the
             * Configuration in a Caching node.
             * eg.
             *  'caching' => array (
             *        'remote_datasource' => 'database',
             *        'dynamic_ds' => 'YourCachingExtensionClassName'
             *  )
             *
             * @since Symphony 2.4
             * @delegate AddCachingOpportunity
             * @param string $context
             * '/system/preferences/'
             * @param XMLElement $wrapper
             *  An XMLElement of the current Caching fieldset
             * @param string $config_path
             *  The node in the Configuration where this information will be stored. Read only.
             * @param array $available_caches
             *  An array of the available cache providers
             * @param array $errors
             *  An array of errors
             */
            Symphony::ExtensionManager()->notifyMembers('AddCachingOpportunity', '/system/preferences/', array(
                'wrapper' => &$group,
                'config_path' => 'caching',
                'available_caches' => $caches,
                'errors' => $this->_errors,
            ));

            $this->Form->appendChild($group);
        }

        // Get available xslt processors
        $processors = Symphony::ExtensionManager()->getProvidersOf('xslt_processing');
        $processors[] = [
            'class' => '\\XsltProcess', 'name' => 'Default (XSLT 1.0)',
        ];

        if (count($processors) > 1) {
            $group = new XMLElement('fieldset', null, array('class' => 'settings condensed'));
            $group->appendChild(new XMLElement('legend', __('XSLT')));

            $label = Widget::Label(__('Processor'));

            $options = [];
            foreach ($processors as $p) {
                $options[] = [
                    $p['class'],
                    Symphony::Configuration()->get('processor', 'xslt') == $p['class'],
                    $p['name'],
                ];
            }

            $select = Widget::Select('settings[xslt][processor]', $options, ['class' => 'picker', 'data-interactive' => 'data-interactive']);
            $label->appendChild($select);
            $group->appendChild($label);
            $this->Form->appendChild($group);
        }

        /*
         * Add Extension custom preferences. Use the $wrapper reference to append objects.
         *
         * @delegate AddCustomPreferenceFieldsets
         * @param string $context
         * '/system/preferences/'
         * @param XMLElement $wrapper
         *  An XMLElement of the current page
         * @param array $errors
         *  An array of errors
         */
        Symphony::ExtensionManager()->notifyMembers('AddCustomPreferenceFieldsets', '/system/preferences/', array(
            'wrapper' => &$this->Form,
            'errors' => $this->_errors,
        ));

        $div = new XMLElement('div');
        $div->setAttribute('class', 'actions');

        $version = new XMLElement('p', 'Symphony '.Symphony::Configuration()->get('version', 'symphony'), array(
            'id' => 'version',
        ));
        $div->appendChild($version);

        $attr = array('accesskey' => 's');

        if (!$bIsWritable) {
            $attr['disabled'] = 'disabled';
        }

        $div->appendChild(Widget::Input('action[save]', __('Save Changes'), 'submit', $attr));

        $this->Form->appendChild($div);
    }

    public function action()
    {
        // Do not proceed if the config file cannot be changed
        if (false === General::checkFileWritable(CONFIG)) {
            redirect(SYMPHONY_URL.'/system/preferences/');
        }

        /*
         * Extensions can listen for any custom actions that were added
         * through `AddCustomPreferenceFieldsets` or `AddCustomActions`
         * delegates.
         *
         * @delegate CustomActions
         * @param string $context
         * '/system/preferences/'
         */
        Symphony::ExtensionManager()->notifyMembers('CustomActions', '/system/preferences/');

        if (isset($_POST['action']['save'])) {
            $settings = filter_var_array($_POST['settings'], FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
            /*
             * Just prior to saving the preferences and writing them to the `CONFIG`
             * Allows extensions to preform custom validation logic on the settings.
             *
             * @delegate Save
             * @param string $context
             * '/system/preferences/'
             * @param array $settings
             *  An array of the preferences to be saved, passed by reference
             * @param array $errors
             *  An array of errors passed by reference
             */
            Symphony::ExtensionManager()->notifyMembers('Save', '/system/preferences/', array(
                'settings' => &$settings,
                'errors' => &$this->_errors,
            ));

            if (!is_array($this->_errors) || empty($this->_errors)) {
                if (is_array($settings) && !empty($settings)) {
                    Symphony::Configuration()->setArray($settings, false);
                }

                if (Symphony::Configuration()->write()) {
                    if (function_exists('opcache_invalidate')) {
                        @opcache_invalidate(CONFIG, true);
                    }

                    redirect(SYMPHONY_URL.'/system/preferences/success/');
                }
            }
        }
    }
}
