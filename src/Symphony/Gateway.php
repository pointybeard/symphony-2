<?php

namespace Symphony\Symphony;

/**
 * The Gateway class provides a standard way to interact with other pages.
 * By default it is essentially a wrapper for CURL, but if that is not available
 * it falls back to use sockets.
 *
 * @example
 *  `
 * $ch = new Gateway;
 * $ch->init('http://www.example.com/');
 * $ch->setopt('POST', 1);
 * $ch->setopt('POSTFIELDS', array('fred' => 1, 'happy' => 'yes'));
 * print $ch->exec();
 * `
 */
class Gateway
{
    /**
     * Constant used to explicitly bypass CURL and use Sockets to
     * complete the request.
     *
     * @var string
     */
    const FORCE_SOCKET = 'socket';

    /**
     * An associative array of some common ports for HTTP, HTTPS
     * and FTP. Port cannot be null when using Sockets.
     *
     * @var array
     */
    private static $ports = [
        'http' => 80,
        'https' => 443,
        'ftp' => 21,
    ];

    /**
     * The URL for the request, as string. This may be a full URL including
     * any basic authentication. It will be parsed and applied to CURL using
     * the correct options.
     *
     * @var string
     */
    private $url = null;

    /**
     * The hostname of the request, as parsed by parse_url.
     *
     * @see http://php.net/manual/en/function.parse-url.php
     *
     * @var string
     */
    private $host = null;

    /**
     * The protocol of the URL in the request, as parsed by parse_url
     * Defaults to http://.
     *
     * @see http://php.net/manual/en/function.parse-url.php
     *
     * @var string
     */
    private $scheme = 'http://';

    /**
     * The port of the URL in the request, as parsed by parse_url.
     *
     * @see http://php.net/manual/en/function.parse-url.php
     *
     * @var int
     */
    private $port = null;

    /**
     * The path of the URL in the request, as parsed by parse_url.
     *
     * @see http://php.net/manual/en/function.parse-url.php
     *
     * @var string
     */
    private $path = null;

    /**
     * The method to request the URL. By default, this is GET.
     *
     * @var string
     */
    private $method = 'GET';

    /**
     * The content-type of the request, defaults to application/x-www-form-urlencoded.
     *
     * @var string
     */
    private $contentType = 'application/x-www-form-urlencoded; charset=utf-8';

    /**
     * The user agent for the request, defaults to Symphony.
     *
     * @var string
     */
    private $agent = 'Symphony';

    /**
     * A URL encoded string of the `$_POST` fields, as built by
     * http_build_query().
     *
     * @see http://php.net/manual/en/function.http-build-query.php
     *
     * @var string
     */
    private $postfields = '';

    /**
     * Whether to the return the Header with the result of the request.
     *
     * @var bool
     */
    private $returnHeaders = false;

    /**
     * The timeout in seconds for the request, defaults to 4.
     *
     * @var int
     */
    private $timeout = 4;

    /**
     * An array of custom headers to pass with the request.
     *
     * @var array
     */
    private $headers = [];

    /**
     * An array of custom options for the CURL request, this
     * can be any option as listed on the PHP manual.
     *
     * @see http://php.net/manual/en/function.curl-setopt.php
     *
     * @var array
     */
    private $customOpts = [];

    /**
     * An array of information about the request after it has
     * been executed. At minimum, regardless of if CURL or Sockets
     * are used, the HTTP Code, URL and Content Type will be returned.
     *
     * @see http://php.net/manual/en/function.curl-getinfo.php
     */
    private $infoLast = [];

    /**
     * Mimics curl_init in that a URL can be provided.
     *
     * @param string $url
     *                    A full URL string to use for the request, this can include
     *                    basic authentication which will automatically set the
     *                    correct options for the CURL request. Defaults to null
     */
    public function init($url = null)
    {
        if (null !== $url) {
            $this->setopt('URL', $url);
        }
    }

    /**
     * Checks to the see if CURL is available, if it isn't, false will
     * be returned, and sockets will be used.
     *
     * @return bool
     */
    public static function isCurlAvailable()
    {
        return function_exists('curl_init');
    }

    /**
     * Resets `$this->postfields` variable to an empty string.
     */
    public function flush()
    {
        $this->postfields = '';
    }

    /**
     * A basic wrapper that simulates the curl_setopt function. Any
     * options that are not recognised by Symphony will fallback to
     * being added to the `$custom_opt` array. Any options in `$custom_opt`
     * will be applied on executed using curl_setopt. Custom options are not
     * available for Socket requests. The benefit of using this function is for
     * convienience as it performs some basic preprocessing for some options
     * such as 'URL', which will take a full formatted URL string and set any
     * authentication or SSL curl options automatically.
     *
     * @see http://php.net/manual/en/function.curl-setopt.php
     *
     * @param string $opt
     *                      A string representing a CURL constant. Symphony will intercept the
     *                      following, URL, POST, POSTFIELDS, USERAGENT, HTTPHEADER,
     *                      RETURNHEADERS, CONTENTTYPE and TIMEOUT. Any other values
     *                      will be saved in the `$custom_opt` array.
     * @param mixed  $value
     *                      The value of the option, usually boolean or a string. Consult the
     *                      setopt documentation for more information.
     */
    public function setopt($opt, $value)
    {
        switch ($opt) {
            case 'URL':
                $this->url = $value;
                $urlParsed = parse_url($value);
                $this->host = $urlParsed['host'];

                if (isset($urlParsed['scheme']) && strlen(trim($urlParsed['scheme'])) > 0) {
                    $this->scheme = $urlParsed['scheme'];
                }

                if (isset($urlParsed['port'])) {
                    $this->port = $urlParsed['port'];
                }

                if (isset($urlParsed['path'])) {
                    $this->path = $urlParsed['path'];
                }

                if (isset($urlParsed['query'])) {
                    $this->path .= '?'.$urlParsed['query'];
                }

                // Allow basic HTTP authentiction
                if (isset($urlParsed['user']) && isset($urlParsed['pass'])) {
                    $this->setopt(CURLOPT_USERPWD, sprintf('%s:%s', $urlParsed['user'], $urlParsed['pass']));
                    $this->setopt(CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                }

                // Better support for HTTPS requests
                if ('https' == $urlParsed['scheme']) {
                    $this->setopt(CURLOPT_SSL_VERIFYPEER, false);
                }
                break;
            case 'POST':
            case 'GET':
            case 'PUT':
            case 'DELETE':
                $this->method = (1 == $value ? $opt : 'GET');
                break;
            case 'POSTFIELDS':
                if (is_array($value) && !empty($value)) {
                    $this->postfields = http_build_query($value);
                } else {
                    $this->postfields = $value;
                }
                break;
            case 'USERAGENT':
                $this->agent = $value;
                break;
            case 'HTTPHEADER':
                // merge the values, so multiple calls won't erase other values
                if (is_array($value)) {
                    $this->headers = array_merge($this->headers, $value);
                } else {
                    $this->headers[] = $value;
                }
                break;
            case 'RETURNHEADERS':
                $this->returnHeaders = (1 == intval($value) ? true : false);
                break;
            case 'CONTENTTYPE':
                $this->contentType = $value;
                break;
            case 'TIMEOUT':
                $this->timeout = max(1, intval($value));
                break;
            default:
                $this->customOpts[$opt] = $value;
                break;
        }
    }

    /**
     * Executes the request using Curl unless it is not available
     * or this function has explicitly been told not by providing
     * the `Gateway::FORCE_SOCKET` constant as a parameter. The function
     * will apply all the options set using `curl_setopt` before
     * executing the request. Information about the transfer is
     * available using the `getInfoLast()` function. Should Curl not be
     * available, this function will fallback to using Sockets with `fsockopen`.
     *
     * @see toolkit.Gateway#getInfoLast()
     *
     * @param string $force_connection_method
     *                                        Only one valid parameter, `Gateway::FORCE_SOCKET`
     *
     * @return string|bool
     *                     The result of the transfer as a string. If any errors occur during
     *                     a socket request, false will be returned.
     */
    public function exec($force_connection_method = null)
    {
        if (self::FORCE_SOCKET !== $force_connection_method && self::isCurlAvailable()) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, sprintf(
                '%s://%s%s%s',
                $this->scheme,
                $this->host,
                (null !== $this->port ? ':'.$this->port : null),
                $this->path
            ));
            curl_setopt($ch, CURLOPT_HEADER, $this->returnHeaders);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
            curl_setopt($ch, CURLOPT_PORT, $this->port);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

            if (0 == ini_get('safe_mode') && '' == ini_get('open_basedir')) {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            }

            switch ($this->method) {
                case 'POST':
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postfields);
                    break;
                case 'PUT':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postfields);
                    $this->setopt('HTTPHEADER', array('Content-Length:' => strlen($this->postfields)));
                    break;
                case 'DELETE':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postfields);
                    break;
            }

            if (is_array($this->headers) && !empty($this->headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
            }

            if (is_array($this->customOpts) && !empty($this->customOpts)) {
                foreach ($this->customOpts as $opt => $value) {
                    curl_setopt($ch, $opt, $value);
                }
            }

            // Grab the result
            $result = curl_exec($ch);

            $this->infoLast = curl_getinfo($ch);
            $this->infoLast['curl_error'] = curl_errno($ch);

            // Close the connection
            curl_close($ch);

            return $result;
        }

        $start = precision_timer();

        if (null === $this->port) {
            $this->port = (null !== $this->scheme ? self::$ports[$this->scheme] : 80);
        }

        // No CURL is available, use attempt to use normal sockets
        $handle = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

        if (false === $handle) {
            return false;
        }

        $query = $this->method.' '.$this->path.' HTTP/1.1'.PHP_EOL;
        $query .= 'Host: '.$this->host.PHP_EOL;
        $query .= 'Content-type: '.$this->contentType.PHP_EOL;
        $query .= 'User-Agent: '.$this->agent.PHP_EOL;
        $query .= @implode(PHP_EOL, $this->headers);
        $query .= 'Content-length: '.strlen($this->postfields).PHP_EOL;
        $query .= 'Connection: close'.PHP_EOL.PHP_EOL;

        if (in_array($this->method, array('PUT', 'POST', 'DELETE'))) {
            $query .= $this->postfields;
        }

        // send request
        if (!@fwrite($handle, $query)) {
            return false;
        }

        stream_set_blocking($handle, false);
        stream_set_timeout($handle, $this->timeout);

        $status = stream_get_meta_data($handle);
        $response = $dechunked = '';

        // get header
        while (!preg_match('/\\r\\n\\r\\n$/', $header) && !$status['timed_out']) {
            $header .= @fread($handle, 1);
            $status = stream_get_meta_data($handle);
        }

        $status = socket_get_status($handle);

        // Get rest of the page data
        while (!feof($handle) && !$status['timed_out']) {
            $response .= fread($handle, 4096);
            $status = stream_get_meta_data($handle);
        }

        @fclose($handle);

        $end = precision_timer('stop', $start);

        if (preg_match('/Transfer\\-Encoding:\\s+chunked\\r\\n/', $header)) {
            $fp = 0;

            do {
                $byte = '';
                $chunk_size = '';

                do {
                    $chunk_size .= $byte;
                    $byte = substr($response, $fp, 1);
                    ++$fp;
                } while ("\r" !== $byte && '\\r' !== $byte);

                $chunk_size = hexdec($chunk_size); // convert to real number

                if (0 == $chunk_size) {
                    break 1;
                }

                ++$fp;

                $dechunked .= substr($response, $fp, $chunk_size);
                $fp += $chunk_size;

                $fp += 2;
            } while (true);

            $response = $dechunked;
        }

        // Following code emulates part of the function curl_getinfo()
        preg_match('/Content-Type:\s*([^\r\n]+)/i', $header, $match);
        $content_type = $match[1];

        preg_match('/HTTP\/\d+.\d+\s+(\d+)/i', $header, $match);
        $status = $match[1];

        $this->infoLast = array(
            'url' => $this->url,
            'content_type' => $content_type,
            'http_code' => (int) $status,
            'total_time' => $end,
        );

        return ($this->returnHeaders ? $header : null).$response;
    }

    /**
     * Returns some information about the last transfer, this
     * the same output array as expected when calling the
     * `curl_getinfo()` function. If Sockets were used to complete
     * the request instead of CURL, the resulting array will be
     * the HTTP Code, Content Type, URL and Total Time of the resulting
     * request.
     *
     * @see http://php.net/manual/en/function.curl-getinfo.php
     *
     * @return array
     */
    public function getInfoLast()
    {
        return $this->infoLast;
    }
}
