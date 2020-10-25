<?php

//declare(strict_types=1);

namespace Symphony\Symphony;

/**
 * The Lang class loads and manages languages.
 */
class Lang
{
    /**
     * Array of transliterations.
     *
     * @var array
     */
    private static $transliterations;

    /**
     * Code of active language.
     *
     * @var string
     */
    private static $lang;

    /**
     * Context information of all available languages.
     *
     * @var array
     */
    private static $languages;

    /**
     * Array of localized strings.
     *
     * @var array
     */
    private static $dictionary;

    /**
     * Array of localized date and time strings.
     *
     * @var array
     */
    private static $dateTimeDictionary;

    /**
     * Get the current dictionary.
     *
     * @return array
     *               Return the dictionary
     */
    public static function Dictionary()
    {
        return self::$dictionary;
    }

    /**
     * Get a list of either enabled or disabled languages. Example:.
     *
     *      array(
     *          [...]
     *
     *          'en' => array(
     *              'name' => 'English',
     *              'handle' => 'english',
     *              'extensions' => array()
     *          ),
     *
     *          'it' => array(
     *              'name' => 'Italiano',
     *              'handle' => 'italian',
     *              'extensions' => array(
     *                  [...]
     *              )
     *          ),
     *
     *          [...]
     *      )
     *
     * @see toolkit.Lang#createLanguage()
     * @since Symphony 2.3
     *
     * @return array
     *               Return an array of languages (both enabled and disabled)
     */
    public static function Languages()
    {
        return self::$languages;
    }

    /**
     * Get transliterations.
     *
     * @return array
     *               Returns the transliterations array
     */
    public static function Transliterations()
    {
        return self::$transliterations;
    }

    /**
     * Initialize transliterations, datetime dictionary and languages array.
     */
    public static function initialize()
    {
        self::$dictionary = [];

        // Load default datetime strings
        if (empty(self::$dateTimeDictionary)) {
            self::$dateTimeDictionary = include LANG.'/DateTime.php';
        }

        // Load default transliterations
        if (empty(self::$transliterations)) {
            self::$transliterations = include LANG.'/Transliterations.php';
        }

        // Load default English language
        if (empty(self::$languages)) {
            self::$languages = self::createLanguage('en', 'English', 'english');
        }

        // Fetch all available languages
        self::fetch();
    }

    /**
     * Create an array of Language information for internal use.
     *
     * @since Symphony 2.3
     *
     * @param string $code
     *                           Language code, e. g. 'en' or 'pt-br'
     * @param string $name
     *                           Language name
     * @param string $handle     (optional)
     *                           Handle for the given language, used to build a valid 'lang_$handle' extension's handle.
     *                           Defaults to null.
     * @param array  $extensions (optional)
     *                           An array of extensions that support the given language
     *
     * @return array
     *               An array of Language information
     */
    private static function createLanguage($code, $name, $handle = null, array $extensions = array())
    {
        return [
            $code => [
                'name' => $name,
                'handle' => $handle,
                'extensions' => $extensions,
            ],
        ];
    }

    /**
     * Fetch all languages available in the core language folder and the language extensions.
     * The function stores all language information in the private variable `$languages`.
     * It contains an array with the name and handle of each language and an array of all
     * extensions available in that language.
     *
     * @throws UnexpectedValueException
     * @throws RuntimeException
     */
    private static function fetch()
    {
        if (!@is_readable(EXTENSIONS)) {
            return;
        }

        // Fetch extensions
        $extensions = new \FilesystemIterator(EXTENSIONS);

        // Language extensions
        foreach ($extensions as $extension) {
            if ($extension->isFile()) {
                continue;
            }

            // Core translations
            $core_handle = (false !== strpos($extension->getFilename(), 'lang_'))
                ? str_replace('lang_', '', $extension->getFilename())
                : null;

            // Loop over the `/lang` directory of this `$extension` searching for language
            // files. If `/lang` isn't a directory, `UnexpectedValueException` will be
            // thrown.
            try {
                $path = $extension->getPathname().'/lang';
                if (!is_dir($path)) {
                    continue;
                }

                $directory = new \GlobIterator($path.'/lang.*.php');
                foreach ($directory as $file) {
                    include $file->getPathname();

                    // Get language code (chars between lang. and .php)
                    $code = substr($file->getFilename(), 5, -4);
                    $lang = null;
                    $handle = null;
                    $extensions = [];

                    // Set lang, handle and extensions if defined.
                    if (isset(self::$languages[$code])) {
                        $lang = self::$languages[$code];
                        $handle = $lang['handle'];
                        $extensions = $lang['extensions'];
                    }

                    // Core translations
                    if ($core_handle) {
                        $handle = $core_handle;

                    // Extension translations
                    } else {
                        $extensions = array_merge(array($extension->getFilename()), $extensions);
                    }

                    // Merge languages ($about is declared inside the included $file)
                    $temp = self::createLanguage($code, $about['name'], $handle, $extensions);

                    if (isset($lang)) {
                        foreach ($lang as $key => $value) {
                            // Prevent missing or nulled values overwriting existing values
                            // which can occur if a translation file is not correct.
                            if (!isset($temp[$code][$key]) || empty($temp[$code][$key])) {
                                continue;
                            }

                            self::$languages[$code][$key] = $temp[$code][$key];
                        }
                    } else {
                        self::$languages[$code] = $temp[$code];
                    }
                }
            } catch (\Exception $ex) {
                continue;
            }
        }
    }

    /**
     * Set system language, load translations for core and extensions. If the specified language
     * cannot be found, Symphony will default to English.
     *
     * Note: Beginning with Symphony 2.2 translations bundled with extensions will only be loaded
     * when the core dictionary of the specific language is available.
     *
     * @param string $code
     *                            Language code, e. g. 'en' or 'pt-br'
     * @param bool   $checkStatus (optional)
     *                            If false, set the language even if it's not enabled. Defaults to true.
     */
    public static function set($code, $checkStatus = true)
    {
        if (!$code || $code == self::get()) {
            return;
        }

        // Language file available
        if ('en' !== $code && (self::isLanguageEnabled($code) || false === $checkStatus)) {
            // Store desired language code
            self::$lang = $code;

            // Clear dictionary
            self::$dictionary = [];

            // Load core translations
            self::load(vsprintf('%s/lang_%s/lang/lang.%s.php', array(
                EXTENSIONS, self::$languages[$code]['handle'], $code,
            )));

            // Load extension translations
            if (is_array(self::$languages[$code]['extensions'])) {
                foreach (self::$languages[$code]['extensions'] as $extension) {
                    self::load(vsprintf('%s/%s/lang/lang.%s.php', array(
                        EXTENSIONS, $extension, $code,
                    )));
                }
            }

            // Language file unavailable, use default language
        } else {
            self::$lang = 'en';

            // Log error, if possible
            if ('en' !== $code && class_exists('Symphony', false) && \Symphony::Log() instanceof Log) {
                \Symphony::Log()->pushToLog(
                    __('The selected language, %s, could not be found. Using default English dictionary instead.', array($code)),
                    E_ERROR,
                    true
                );
            }
        }
    }

    /**
     * Given a valid language code, this function checks if the language is enabled.
     *
     * @since Symphony 2.3
     *
     * @param string $code
     *                     Language code, e. g. 'en' or 'pt-br'
     *
     * @return bool
     *              If true, the language is enabled
     */
    public static function isLanguageEnabled($code)
    {
        if ('en' == $code) {
            return true;
        }

        $handle = (isset(self::$languages[$code])) ? self::$languages[$code]['handle'] : '';
        $enabled_extensions = [];

        // Fetch list of active extensions
        if (class_exists('Symphony', false) && (null !== \Symphony::ExtensionManager())) {
            $enabled_extensions = \Symphony::ExtensionManager()->listInstalledHandles();
        }

        return in_array('lang_'.$handle, $enabled_extensions);
    }

    /**
     * Load language file. Each language file contains three arrays:
     * about, dictionary and transliterations.
     *
     * @param string $path
     *                     Path of the language file that should be loaded
     */
    private static function load($path)
    {
        // Load language file
        if (file_exists($path)) {
            require $path;
        }

        // Populate dictionary ($dictionary is declared inside $path)
        if (isset($dictionary) && is_array($dictionary)) {
            self::$dictionary = array_merge(self::$dictionary, $dictionary);
        }

        // Populate transliterations ($transliterations is declared inside $path)
        if (isset($transliterations) && is_array($transliterations)) {
            self::$transliterations = array_merge(self::$transliterations, $transliterations);
        }
    }

    /**
     * Get current language.
     *
     * @return string
     */
    public static function get()
    {
        return self::$lang;
    }

    /**
     * This function is an internal alias for `__()`.
     *
     * @since Symphony 2.3
     * @see toolkit.__()
     *
     * @param string $string
     *                          The string that should be translated
     * @param array  $inserts   (optional)
     *                          Optional array used to replace translation placeholders, defaults to NULL
     * @param string $namespace (optional)
     *                          Optional string used to define the namespace, defaults to NULL
     *
     * @return string
     *                Returns the translated string
     */
    public static function translate($string, array $inserts = null, $namespace = null)
    {
        if (null === $namespace && class_exists('Symphony', false)) {
            $namespace = \Symphony::getPageNamespace();
        }

        if (isset($namespace, self::$dictionary[$namespace][$string])) {
            $translated = self::$dictionary[$namespace][$string];
        } elseif (isset(self::$dictionary[$string])) {
            $translated = self::$dictionary[$string];
        } else {
            $translated = $string;
        }

        $translated = empty($translated) ? $string : $translated;

        // Replace translation placeholders
        if (is_array($inserts) && !empty($inserts)) {
            $translated = vsprintf($translated, $inserts);
        }

        return $translated;
    }

    /**
     * Get an array of the codes and names of all languages that are available system wide.
     *
     * Note: Beginning with Symphony 2.2 language files are only available
     * when the language extension is explicitly enabled.
     *
     * @param bool $checkStatus (optional)
     *                          If false, retrieves a list a languages that support core translation
     *
     * @return array
     *               Returns an associative array of language codes and names, e. g. 'en' => 'English'
     */
    public static function getAvailableLanguages($checkStatus = true)
    {
        $languages = [];

        // Get available languages
        foreach (self::$languages as $key => $language) {
            if (self::isLanguageEnabled($key) || (false === $checkStatus && isset($language['handle']))) {
                $languages[$key] = $language['name'];
            }
        }

        // Return languages codes
        return $languages;
    }

    /**
     * Check if Symphony is localised.
     *
     * @return bool
     *              Returns true for localized system, false for English system
     */
    public static function isLocalized()
    {
        return 'en' !== self::get();
    }

    /**
     * Localize dates.
     *
     * @param string $string
     *                       Standard date that should be localized
     *
     * @return string
     *                Return the given date with translated month and day names
     */
    public static function localizeDate($string)
    {
        // Only translate dates in localized environments
        if (self::isLocalized()) {
            foreach (self::$dateTimeDictionary as $value) {
                $string = preg_replace('/\b'.$value.'\b/i', self::translate($value), $string);
            }
        }

        return $string;
    }

    /**
     * Standardize dates.
     *
     * @param string $string
     *                       Localized date that should be standardized
     *
     * @return string
     *                Returns the given date with English month and day names
     */
    public static function standardizeDate($string)
    {
        // Only standardize dates in localized environments
        if (self::isLocalized()) {
            // Translate names to English
            foreach (self::$dateTimeDictionary as $values) {
                $string = preg_replace('/\b'.self::translate($values).'\b/i'.(true === self::isUnicodeCompiled() ? 'u' : null), $values, $string);
            }

            // Replace custom date and time separator with space:
            // This is important, otherwise the `DateTime` constructor may break
            // @todo Test if this separator is still required. It's a hidden setting
            // and users are only aware of it if they go digging/pointed in the right direction
            $separator = \Symphony::Configuration()->get('datetime_separator', 'region');
            if (' ' !== $separator) {
                $string = str_replace($separator, ' ', $string);
            }
        }

        return $string;
    }

    /**
     * Given a string, this will clean it for use as a Symphony handle. Preserves multi-byte characters.
     *
     * @param string $string
     *                                      String to be cleaned up
     * @param int    $max_length
     *                                      The maximum number of characters in the handle
     * @param string $delim
     *                                      All non-valid characters will be replaced with this
     * @param bool   $uriencode
     *                                      Force the resultant string to be uri encoded making it safe for URLs
     * @param bool   $apply_transliteration
     *                                      If true, this will run the string through an array of substitution characters
     * @param array  $additional_rule_set
     *                                      An array of REGEX patterns that should be applied to the `$string`. This
     *                                      occurs after the string has been trimmed and joined with the `$delim`
     *
     * @return string
     *                Returns resultant handle
     */
    public static function createHandle($string, $max_length = 255, $delim = '-', $uriencode = false, $apply_transliteration = true, $additional_rule_set = null)
    {
        // Use the transliteration table if provided
        if (true === $apply_transliteration) {
            $string = self::applyTransliterations($string);
        }

        return General::createHandle($string, $max_length, $delim, $uriencode, $additional_rule_set);
    }

    /**
     * Given a string, this will clean it for use as a filename. Preserves multi-byte characters.
     *
     * @param string $string
     *                                      String to be cleaned up
     * @param string $delim
     *                                      Replacement for invalid characters
     * @param bool   $apply_transliteration
     *                                      If true, umlauts and special characters will be substituted
     *
     * @return string
     *                Returns created filename
     */
    public static function createFilename($string, $delim = '-', $apply_transliteration = true)
    {
        // Use the transliteration table if provided
        if (true === $apply_transliteration) {
            $file = pathinfo($string);
            $string = self::applyTransliterations($file['filename']).'.'.$file['extension'];
        }

        return General::createFilename($string, $delim);
    }

    /**
     * This function replaces special characters according to the values stored inside
     * `$transliterations`.
     *
     * @since Symphony 2.3
     *
     * @param string $string
     *                       The string that should be cleaned-up
     *
     * @return mixed
     *               Returns the transliterated string
     */
    private static function applyTransliterations($string)
    {
        // Apply the straight transliterations with strtr as it's much faster
        $string = strtr($string, self::$transliterations['straight']);

        // Apply the regex rules over the resulting $string
        return preg_replace(
            array_keys(self::$transliterations['regexp']),
            array_values(self::$transliterations['regexp']),
            $string
        );
    }

    /**
     * Returns boolean if PHP has been compiled with unicode support. This is
     * useful to determine if unicode modifier's can be used in regular expression's.
     *
     * @see http://stackoverflow.com/questions/4509576/detect-if-pcre-was-built-without-the-enable-unicode-properties-or-enable-utf8
     * @since Symphony 2.2.2
     *
     * @return bool
     */
    public static function isUnicodeCompiled()
    {
        return 1 == @preg_match('/\pL/u', 'a') ? true : false;
    }
}
