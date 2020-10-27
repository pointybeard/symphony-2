<?php

namespace Symphony\Symphony;

/**
 * The Log class acts a simple wrapper to write errors to a file so that it can
 * be read at a later date. There is one Log file in Symphony, stored in the main
 * `LOGS` directory.
 */
class Log
{
    /**
     * A constant for if this message should add to an existing log file.
     *
     * @var int
     */
    const APPEND = 10;

    /**
     * A constant for if this message should overwrite the existing log.
     *
     * @var int
     */
    const OVERWRITE = 11;

    /**
     * The path to this log file.
     *
     * @var string
     */
    private $logPath = null;

    /**
     * An array of log messages to write to the log.
     *
     * @var array
     */
    private $log = [];

    /**
     * The maximise size of the log can reach before it is rotated and a new
     * Log file written started. The units are bytes. Default is -1, which
     * means that the log will never be rotated.
     *
     * @var int
     */
    private $maxSize = -1;

    /**
     * Whether to archive olds logs or not, by default they will not be archived.
     *
     * @var bool
     */
    private $archiveOldLogsEnabled = false;

    /**
     * The filter applied to logs before they are written.
     *
     * @since Symphony 2.7.1
     *
     * @var int
     */
    private $filter = -1;

    /**
     * The date format that this Log entries will be written as. Defaults to
     * Y/m/d H:i:s.
     *
     * @var string
     */
    private $dateTimeFormat = 'Y/m/d H:i:s';

    /**
     * The log constructor takes a path to the folder where the Log should be
     * written to.
     *
     * @param string $path
     *                     The path to the folder where the Log files should be written
     */
    public function __construct($path)
    {
        $this->setLogPath($path);
    }

    /**
     * Setter for the `$logPath`.
     *
     * @param string $path
     *                     The path to the folder where the Log files should be written
     */
    public function setLogPath($path)
    {
        $this->logPath = $path;
    }

    /**
     * Accessor for the `$logPath`.
     *
     * @return string
     */
    public function getLogPath()
    {
        return $this->logPath;
    }

    /**
     * Accessor for the `$log`.
     *
     * @return array
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Setter for the `$archiveOldLogsEnabled`.
     *
     * @param bool $archive
     *                      If true, Log files will be archived using gz when they are rotated,
     *                      otherwise they will just be overwritten when they are due for rotation
     */
    public function setArchive($archive)
    {
        $this->archiveOldLogsEnabled = $archive;
    }

    /**
     * Setter for the `$maxSize`.
     *
     * @param int $size
     *                  The size, in bytes, that the Log can reach before it is rotated
     */
    public function setMaxSize($size)
    {
        $this->maxSize = \General::intval($size);
    }

    /**
     * Setter for the `$filter`.
     *
     * @since Symphony 2.7.1
     *
     * @param mixed $filter
     *                      The filter used on log $type parameter
     */
    public function setFilter($filter)
    {
        $this->filter = \General::intval($filter);
    }

    /**
     * Setter for the `$_date_format`.
     *
     * @since Symphony 2.2
     * @see http://au.php.net/manual/en/function.date.php
     *
     * @param string $format
     *                       Takes a valid date format using the PHP date tokens
     */
    public function setDateTimeFormat($format)
    {
        if (empty($format)) {
            throw new Exceptions\SymphonyException('Datetime format can not be empty');
        }
        $this->dateTimeFormat = $format;
    }

    /**
     * Given a PHP error constant, return a human readable name. Uses the
     * `GenericErrorHandler::$errorTypeStrings` array to return
     * the name.
     *
     * @see core.GenericErrorHandler::$errorTypeStrings
     *
     * @param int $type
     *                  A PHP error constant
     *
     * @return string
     *                A human readable name of the error constant, or if the type is not
     *                found, UNKNOWN
     */
    private function __defineNameString($type)
    {
        if (isset(Handlers\GenericErrorHandler::$errorTypeStrings[$type])) {
            return Handlers\GenericErrorHandler::$errorTypeStrings[$type];
        }

        return 'UNKNOWN';
    }

    /**
     * Function will return the last message added to `$log` and remove
     * it from the array.
     *
     * @return array|bool
     *                    Returns an associative array of a log message, containing the type of the log
     *                    message, the actual message and the time at the which it was added to the log.
     *                    If the log is empty, this function removes false.
     */
    public function popFromLog()
    {
        if (!empty($this->log)) {
            return array_pop($this->log);
        }

        return false;
    }

    /**
     * Given a message, this function will add it to the internal `$log`
     * so that it can be written to the Log. Optional parameters all the message to
     * be immediately written, insert line breaks or add to the last log message.
     *
     * @param string $message
     *                           The message to add to the Log
     * @param int    $type
     *                           A PHP error constant for this message, defaults to E_NOTICE.
     *                           If null or 0, will be converted to E_ERROR.
     * @param bool   $writeToLog
     *                           If set to true, this message will be immediately written to the log. By default
     *                           this is set to false, which means that it will only be added to the array ready
     *                           for writing
     * @param bool   $addbreak
     *                           To be used in conjunction with `$writeToLog`, this will add a line break
     *                           before writing this message in the log file. Defaults to true.
     * @param bool   $append
     *                           If set to true, the given `$message` will be append to the previous log
     *                           message found in the `$log` array
     *
     * @return bool|null
     *                   If `$writeToLog` is passed, this function will return boolean, otherwise
     *                   void
     */
    public function pushToLog($message, $type = E_NOTICE, $writeToLog = false, $addbreak = true, $append = false)
    {
        if (!$type) {
            $type = E_ERROR;
        }

        if ($append) {
            $this->log[count($this->log) - 1]['message'] = $this->log[count($this->log) - 1]['message'].$message;
        } else {
            array_push($this->log, array('type' => $type, 'time' => time(), 'message' => $message));
            $message = DateTimeObj::get($this->dateTimeFormat).' > '.$this->__defineNameString($type).': '.$message;
        }

        if ($writeToLog && (-1 === $this->filter || ($this->filter & $type))) {
            return $this->writeToLog($message, $addbreak);
        }
    }

    /**
     * This function will write the given message to the log file. Messages will be appended
     * the existing log file.
     *
     * @param string $message
     *                         The message to add to the Log
     * @param bool   $addbreak
     *                         To be used in conjunction with `$writeToLog`, this will add a line break
     *                         before writing this message in the log file. Defaults to true.
     *
     * @return bool
     *              Returns true if the message was written successfully, false otherwise
     */
    public function writeToLog($message, $addbreak = true)
    {
        if (file_exists($this->logPath) && !is_writable($this->logPath)) {
            $this->pushToLog('Could not write to Log. It is not readable.');

            return false;
        }

        $permissions = class_exists('Symphony', false) ? \Symphony::Configuration()->get('write_mode', 'file') : '0664';

        return \General::writeFile($this->logPath, $message.($addbreak ? PHP_EOL : ''), $permissions, 'a+');
    }

    /**
     * Given an Throwable, this function will add it to the internal `$log`
     * so that it can be written to the Log.
     *
     * @since Symphony 2.3.2
     * @since Symphony 2.7.0
     *  This function works with both Exceptions and Throwable
     *  Supporting both PHP 5.6 and 7 forces use to not qualify the $e parameter
     *
     * @param Throwable $exception
     * @param bool      $writeToLog
     *                              If set to true, this message will be immediately written to the log. By default
     *                              this is set to false, which means that it will only be added to the array ready
     *                              for writing
     * @param bool      $addbreak
     *                              To be used in conjunction with `$writeToLog`, this will add a line break
     *                              before writing this message in the log file. Defaults to true.
     * @param bool      $append
     *                              If set to true, the given `$message` will be append to the previous log
     *                              message found in the `$log` array
     *
     * @return bool|null
     *                   If `$writeToLog` is passed, this function will return boolean, otherwise
     *                   void
     */
    public function pushExceptionToLog($exception, $writeToLog = false, $addbreak = true, $append = false)
    {
        $message = sprintf(
            '%s %s - %s on line %d of %s',
            get_class($exception),
            $exception->getCode(),
            $exception->getMessage(),
            $exception->getLine(),
            $exception->getFile()
        );

        return $this->pushToLog($message, $exception->getCode(), $writeToLog, $addbreak, $append);
    }

    /**
     * Given an method name, this function will properly format a message
     * and pass it down to `pushToLog()`.
     *
     * @see Log::pushToLog()
     * @since Symphony 2.7.0
     *
     * @param string $method
     *                                        The name of the deprecated call
     * @param string $alternative
     *                                        The name of the new method to use
     * @param array  $opts                    (optional)
     * @param string $opts.message-format
     *                                        The sprintf format to apply to $method
     * @param string $opts.alternative-format
     *                                        The sprintf format to apply to $alternative
     * @param string $opts.removal-format
     *                                        The sprintf format to apply to $opts.removal-version
     * @param string $opts.removal-version
     *                                        The Symphony version at which the removal is planned
     * @param bool   $opts.write-to-log
     *                                        If set to true, this message will be immediately written to the log. By default
     *                                        this is set to false, which means that it will only be added to the array ready
     *                                        for writing
     * @param bool   $opts.addbreak
     *                                        To be used in conjunction with `$opts.write-to-log`, this will add a line break
     *                                        before writing this message in the log file. Defaults to true.
     * @param bool   $opts.append
     *                                        If set to true, the given `$message` will be append to the previous log
     *                                        message found in the `$log` array
     * @param bool   $opts.addtrace
     *                                        If set to true, the caller of the function will be added. Defaults to true.
     *
     * @return bool|null
     *                   If `$writeToLog` is passed, this function will return boolean, otherwise
     *                   void
     */
    public function pushDeprecateWarningToLog($method, $alternative = null, array $opts = [])
    {
        $defaults = array(
            'message-format' => __('The method `%s` is deprecated.'),
            'alternative-format' => __('Please use `%s` instead.'),
            'removal-format' => __('It will be removed in Symphony %s.'),
            'removal-version' => '3.0.0',
            'write-to-log' => true,
            'addbreak' => true,
            'append' => false,
            'addtrace' => true,
        );
        $opts = array_replace($defaults, $opts);

        $message = sprintf($opts['message-format'], $method);
        if (!empty($opts['removal-version'])) {
            $message .= ' '.sprintf($opts['removal-format'], $opts['removal-version']);
        }
        if (!empty($alternative)) {
            $message .= ' '.sprintf($opts['alternative-format'], $alternative);
        }
        if (true === $opts['addtrace']) {
            if (version_compare(phpversion(), '5.4', '<')) {
                $trace = debug_backtrace(0);
            } else {
                $trace = debug_backtrace(0, 3);
            }
            $index = isset($trace[2]['class']) ? 2 : 1;
            $caller = $trace[$index]['class'].'::'.$trace[$index]['function'].'()';
            $file = basename($trace[$index - 1]['file']);
            $line = $trace[$index - 1]['line'];
            $message .= " Called from `$caller` in $file at line $line";
        }

        return $this->pushToLog($message, E_DEPRECATED, $opts['write-to-log'], $opts['addbreak'], $opts['append']);
    }

    /**
     * The function handles the rotation of the log files. By default it will open
     * the current log file, 'main', which is written to `$logPath` and
     * check it's file size doesn't exceed `$maxSize`. If it does, the log
     * is appended with a date stamp and if `$archiveOldLogsEnabled` has been set, it will
     * be archived and stored. If a log file has exceeded it's size, or `Log::OVERWRITE`
     * flag is set, the existing log file is removed and a new one created. Essentially,
     * if a log file has not reached it's `$maxSize` and the the flag is not
     * set to `Log::OVERWRITE`, this function does nothing.
     *
     * @see http://au.php.net/manual/en/function.intval.php
     *
     * @param int $flag
     *                  One of the Log constants, either `Log::APPEND` or `Log::OVERWRITE`
     *                  By default this is `Log::APPEND`
     * @param int $mode
     *                  The file mode used to apply to the archived log, by default this is 0777. Note that this
     *                  parameter is modified using PHP's intval function with base 8.
     *
     * @throws Exception
     *
     * @return int
     *             Returns 1 if the log was overwritten, or 2 otherwise
     */
    public function open($flag = self::APPEND, $mode = 0777)
    {
        if (!file_exists($this->logPath)) {
            $flag = self::OVERWRITE;
        }

        if (self::APPEND == $flag && file_exists($this->logPath) && is_readable($this->logPath)) {
            if ($this->maxSize > 0 && filesize($this->logPath) > $this->maxSize) {
                $flag = self::OVERWRITE;

                if ($this->archiveOldLogsEnabled) {
                    $this->close();
                    $file = $this->logPath.DateTimeObj::get('Ymdh').'.gz';
                    if (function_exists('gzopen64')) {
                        $handle = gzopen64($file, 'w9');
                    } else {
                        $handle = gzopen($file, 'w9');
                    }
                    gzwrite($handle, file_get_contents($this->logPath));
                    gzclose($handle);
                    chmod($file, intval($mode, 8));
                }
            }
        }

        if (self::OVERWRITE == $flag) {
            \General::deleteFile($this->logPath);

            $this->writeToLog('============================================', true);
            $this->writeToLog('Log Created: '.DateTimeObj::get('c'), true);
            $this->writeToLog('============================================', true);

            @chmod($this->logPath, intval($mode, 8));

            return 1;
        }

        return 2;
    }

    /**
     * Writes a end of file block at the end of the log file with a datetime
     * stamp of when the log file was closed.
     */
    public function close()
    {
        $this->writeToLog('============================================', true);
        $this->writeToLog('Log Closed: '.DateTimeObj::get('c'), true);
        $this->writeToLog('============================================'.PHP_EOL.PHP_EOL, true);
    }

    /* Initialises the log file by writing into it the log name, the date of
     * creation, the current Symphony version and the current domain.
     *
     * @param string $name
     *  The name of the log being initialised
     */
    public function initialise($name)
    {
        $version = (null === \Symphony::Configuration()) ? VERSION : \Symphony::Configuration()->get('version', 'symphony');

        $this->writeToLog($name, true);
        $this->writeToLog('Opened:  '.DateTimeObj::get('c'), true);
        $this->writeToLog('Version: '.$version, true);
        $this->writeToLog('Domain:  '.DOMAIN, true);
        $this->writeToLog('--------------------------------------------', true);
    }
}
