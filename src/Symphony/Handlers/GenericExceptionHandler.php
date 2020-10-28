<?php

namespace Symphony\Symphony\Handlers;

use Symphony\Symphony;
use Symphony\Symphony\Exceptions;

/**
 * GenericExceptionHandler will handle any uncaught exceptions thrown in
 * Symphony. Additionally, all errors in Symphony that are raised to Exceptions
 * will be handled by this class.
 * It is possible for Exceptions to be caught by their own `ExceptionHandler` which can
 * override the `render` function so that it can be displayed to the user appropriately.
 */
class GenericExceptionHandler
{
    /**
     * Whether the `GenericExceptionHandler` should handle exceptions. Defaults to true.
     *
     * @var bool
     */
    public static $enabled = true;

    /**
     * An instance of the Symphony Log class, used to write errors to the log.
     *
     * @var Log
     */
    private static $log = null;

    /**
     * Initialise will set the error handler to be the `__CLASS__::handler` function.
     *
     * @param Log $Log
     *                 An instance of a Symphony Log object to write errors to
     */
    public static function initialise(\Log $log = null): void
    {
        if (null !== $log) {
            self::$log = $log;
        }

        set_exception_handler(array(__CLASS__, 'handler'));
        register_shutdown_function(array(__CLASS__, 'shutdown'));
    }

    /**
     * Retrieves a window of lines before and after the line where the error
     * occurred so that a developer can help debug the exception.
     *
     * @param int    $line
     *                       The line where the error occurred
     * @param string $file
     *                       The file that holds the logic that caused the error
     * @param int    $window
     *                       The number of lines either side of the line where the error occurred
     *                       to show
     *
     * @return array
     */
    protected static function __nearbyLines(int $line, string $file, int $window = 5): array
    {
        if (false == file_exists($file)) {
            return [];
        }

        return array_slice(file($file), ($line - 1) - $window, $window * 2, true);
    }

    /**
     * This function's goal is to validate the `$e` parameter in order to ensure
     * that the object is an `Exception` or a `Throwable` instance.
     *
     * @since Symphony 2.7.0
     *
     * @param Throwable $e
     *                     The Throwable object that will be validated
     *
     * @return bool
     *              true when valid, false otherwise
     */
    public static function isValidThrowable($e): bool
    {
        return $e instanceof \Exception || $e instanceof \Throwable;
    }

    /**
     * The handler function is given an Throwable and will call it's render
     * function to display the Throwable to a user. After calling the render
     * function, the output is displayed and then exited to prevent any further
     * logic from occurring.
     *
     * @since Symphony 2.7.0
     *  This function works with both Exception and Throwable
     *  Supporting both PHP 5.6 and 7 forces use to not qualify the $e parameter
     *
     * @param Throwable $e
     *                     The Throwable object
     *
     * @return string
     *                The result of the Throwable's render function
     */
    public static function handler($exception): string
    {
        $output = '';

        try {
            // If this is anything other than a SymphonyException, e.g. TypeError,
            // then let it fall through and be handled by the GenericExceptionHandler
            // rather than rendered as a Symphony 404 page
            if (false == ($exception instanceof Exceptions\SymphonyException)) {
                $e = $exception;

            // This is a SymphonyException so we don't need to see much in the
            // way of errors. Instead, show a 404 page
            } else {
                // Instead of just throwing an empty page, return a 404 page.
                if (true !== self::$enabled) {
                    $e = new Exceptions\FrontendPageNotFoundException($exception);
                }

                // Validate the type, resolve to a 404 if not valid
                if (!static::isValidThrowable($exception)) {
                    $e = new Exceptions\FrontendPageNotFoundException($exception);
                }
            }

            $exceptionType = array_pop(explode('\\', get_class($e)));

            if (class_exists("\\Symphony\\Symphony\\Handlers\\{$exceptionType}Handler") && method_exists("\\Symphony\\Symphony\\Handlers\\{$exceptionType}Handler", 'render')) {
                $class = "{$exceptionType}Handler";
            } else {
                $class = __CLASS__;
            }

            // Exceptions should be logged if they are not caught.
            if (self::$log instanceof Symphpony\Log) {
                self::$log->pushExceptionToLog($e, true);
            }

            $output = call_user_func([$class, 'render'], $e);

            // If an exception was raised trying to render the exception, fall back
        // to the generic exception handler
        } catch (\Exception $exception) {
            try {
                $output = call_user_func([$this, 'render'], $exception);

                // If the generic exception handler couldn't do it, well we're in bad
            // shape, just output a plaintext response!
            } catch (\Exception $e) {
                echo '<pre>';
                echo 'A severe error occurred whilst trying to handle an exception, check the Symphony log for more details'.PHP_EOL;
                echo $e->getMessage().' on '.$e->getLine().' of file '.$e->getFile().PHP_EOL;
                exit;
            }
        }

        // Pending nothing disasterous, we should have `$e` and `$output` values here.
        if (false == headers_sent()) {
            cleanup_session_cookies();

            // Inspect the exception to determine the best status code
            $httpStatus = null;
            if ($e instanceof Exceptions\SymphonyErrorPageException) {
                $httpStatus = $e->getHttpStatusCode();
            } elseif ($e instanceof Exceptions\FrontendPageNotFoundException) {
                $httpStatus = Symphony\AbstractPage::HTTP_STATUS_NOT_FOUND;
            }

            if (false == $httpStatus || Symphony\AbstractPage::HTTP_STATUS_OK == $httpStatus) {
                $httpStatus = Symphony\AbstractPage::HTTP_STATUS_ERROR;
            }

            Symphony\AbstractPage::renderStatusCode($httpStatus);
            header('Content-Type: text/html; charset=utf-8');
        }

        echo $output;
        exit;
    }

    private static function getTemplatePathFromName(string $name): ?string
    {
        $bits = explode('.', strtolower($name));
        $bits = array_map('ucfirst', $bits);

        return implode('/', $bits);
    }

    /**
     * Returns the path to the error-template by looking at the `WORKSPACE/template/`
     * directory, then at the `TEMPLATES`  directory for the convention `*.tpl`. If
     * the template is not found, `false` is returned.
     *
     * @since Symphony 2.3
     *
     * @param string $name
     *                     Name of the template
     *
     * @return string|false
     *                      String, which is the path to the template if the template is found,
     *                      false otherwise
     */
    public static function getTemplate(string $name)
    {
        $format = '%s/%s.tpl';

        // Keep this for backwards compatiblity
        $template = sprintf($format, WORKSPACE.'/template', $name);
        if (file_exists($template)) {
            return $template;
        }

        $template = sprintf($format, TEMPLATE, self::getTemplatePathFromName($name));
        if (file_exists($template)) {
            return $template;
        }

        return false;
    }

    /**
     * The render function will take an Throwable and output a HTML page.
     *
     * @since Symphony 2.7.0
     *  This function works with both Exception and Throwable
     *
     * @param Throwable $e
     *                     The Throwable object
     *
     * @return string
     *                An HTML string
     */
    public static function render(\Throwable $ex): string
    {
        $lines = null;

        foreach (self::__nearByLines($ex->getLine(), $ex->getFile()) as $line => $string) {
            $lines .= sprintf(
                '<li%s><strong>%d</strong> <code>%s</code></li>',
                (($line + 1) == $ex->getLine() ? ' class="error"' : null),
                ++$line,
                str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', htmlspecialchars($string))
            );
        }

        $trace = null;

        foreach ($ex->getTrace() as $t) {
            $trace .= sprintf(
                '<li><code><em>[%s:%d]</em></code></li><li><code>&#160;&#160;&#160;&#160;%s%s%s();</code></li>',
                (isset($t['file']) ? $t['file'] : null),
                (isset($t['line']) ? $t['line'] : null),
                (isset($t['class']) ? $t['class'] : null),
                (isset($t['type']) ? $t['type'] : null),
                $t['function']
            );
        }

        $queries = null;

        if (true == is_object(\Symphony::Database())) {
            $debug = \Symphony::Database()->debug();

            if (!empty($debug)) {
                foreach ($debug as $query) {
                    $queries .= sprintf(
                        '<li><em>[%01.4f]</em><code> %s;</code> </li>',
                        (isset($query['execution_time']) ? $query['execution_time'] : null),
                        htmlspecialchars($query['query'])
                    );
                }
            }
        }

        return self::renderHtml(
            'fatalerror.generic',
            ($ex instanceof \ErrorException ? GenericErrorHandler::$errorTypeStrings[$ex->getSeverity()] : 'Fatal Error'),
            $ex->getMessage(),
            $ex->getFile(),
            $ex->getLine(),
            $lines,
            $trace,
            $queries
        );
    }

    /**
     * The shutdown function will capture any fatal errors and format them as a
     * usual Symphony page.
     *
     * @since Symphony 2.4
     */
    public static function shutdown()
    {
        $last_error = error_get_last();

        if (false == (null === $last_error) && E_ERROR === $last_error['type']) {
            $code = $last_error['type'];
            $message = $last_error['message'];
            $file = $last_error['file'];
            $line = $last_error['line'];

            try {
                // Log the error message
                if (self::$log instanceof Symphony\Log) {
                    self::$log->pushToLog(sprintf(
                        '%s %s: %s%s%s',
                        __CLASS__,
                        $code,
                        $message,
                        ($line ? " on line $line" : null),
                        ($file ? " of file $file" : null)
                    ), $code, true);
                }

                ob_clean();

                // Display the error message
                echo self::renderHtml(
                    'fatalerror.fatal',
                    'Fatal Error',
                    $message,
                    $file,
                    $line
                );
            } catch (Exception $e) {
                echo '<pre>';
                echo 'A severe error occurred whilst trying to handle an exception, check the Symphony log for more details'.PHP_EOL;
                echo $e->getMessage().' on '.$e->getLine().' of file '.$e->getFile().PHP_EOL;
            }
        }
    }

    /**
     * This function will fetch the desired `$template`, and output the
     * Throwable in a user friendly way.
     *
     * @since Symphony 2.4
     *
     * @param string $template
     *                         The template name, which should correspond to something in the TEMPLATE
     *                         directory, eg `fatalerror.fatal`.
     *
     * @since Symphony 2.7.0
     *  This function works with both Exception and Throwable
     *
     * @param string $heading
     * @param string $message
     * @param string $file
     * @param string $line
     * @param string $lines
     * @param string $trace
     * @param string $queries
     *
     * @return string
     *                The HTML of the formatted error message
     */
    public static function renderHtml($template, $heading, $message, $file = null, $line = null, $lines = null, $trace = null, $queries = null): string
    {
        $html = sprintf(
            file_get_contents(self::getTemplate($template)),
            $heading,
            Symphony\General::unwrapCDATA($message),
            $file,
            $line,
            $lines,
            $trace,
            $queries
        );

        $html = str_replace('{ASSETS_URL}', defined('ASSETS_URL') ? ASSETS_URL : '', $html);
        $html = str_replace('{SYMPHONY_URL}', defined('SYMPHONY_URL') ? SYMPHONY_URL : '', $html);
        $html = str_replace('{URL}', defined('URL') ? URL : '', $html);

        return $html;
    }
}
