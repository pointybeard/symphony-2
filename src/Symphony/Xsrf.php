<?php

namespace Symphony\Symphony;

/**
 * The `XSRF` class provides protection for mitigating XRSF/CSRF attacks.
 *
 * @since Symphony 2.4
 *
 * @author Rich Adams, http://richadams.me
 */
class Xsrf
{
    /**
     * Return's the location of the XSRF tokens in the Session.
     *
     * @return string|null
     */
    public static function getSessionToken()
    {
        $token = null;

        if (isset($_SESSION[__SYM_COOKIE_PREFIX__]['xsrf-token'])) {
            $token = $_SESSION[__SYM_COOKIE_PREFIX__]['xsrf-token'];
        }

        if (is_array($token)) {
            $token = key($token);
        }

        return null === $token ? null : $token;
    }

    /**
     * Adds a token to the Session.
     *
     * @param array $token
     */
    public static function setSessionToken($token = [])
    {
        $_SESSION[__SYM_COOKIE_PREFIX__]['xsrf-token'] = $token;
    }

    /**
     * Removes the token from the Session.
     *
     * @param string $token
     */
    public static function removeSessionToken($token = null)
    {
        if (null === $token) {
            return;
        }

        $_SESSION[__SYM_COOKIE_PREFIX__]['xsrf-token'] = null;
    }

    /**
     * Generates nonce to a desired `$length` using `openssl` where available,
     * falling back to using `/dev/urandom` and a microtime implementation
     * otherwise.
     *
     * @param int $length optional. By default, 30.
     *
     * @return string
     *                base64 encoded, url safe
     */
    public static function generateNonce($length = 30)
    {
        $random = null;
        if ($length < 1) {
            throw new Exception('$length must be greater than 0');
        }

        // Use the new PHP 7 random_bytes call, if available
        if (!$random && function_exists('random_bytes')) {
            $random = random_bytes($length);
        }

        // Get some random binary data from open ssl, if available
        if (!$random && function_exists('openssl_random_pseudo_bytes')) {
            $random = openssl_random_pseudo_bytes($length);
        }

        // Fallback to /dev/urandom
        if (!$random && is_readable('/dev/urandom')) {
            if (false !== ($handle = @fopen('/dev/urandom', 'rb'))) {
                $random = @fread($handle, $length);
                @fclose($handle);
            }
        }

        // Fallback if no random bytes were found
        if (!$random) {
            $random = microtime();

            for ($i = 0; $i < 1000; $i += $length) {
                $random = sha1(microtime().$random);
            }
        }

        // Convert to base64
        $random = base64_encode($random);

        // Replace unsafe chars
        $random = strtr($random, '+/', '-_');
        $random = str_replace('=', '', $random);

        // Truncate the string to specified lengh
        $random = substr($random, 0, $length);

        return $random;
    }

    /**
     * Creates the form input to use to house the token.
     *
     * @return XmlElement
     */
    public static function formToken()
    {
        // <input type="hidden" name="xsrf" value=" . self::getToken() . " />
        $obj = new XmlElement('input');
        $obj->setAttribute('type', 'hidden');
        $obj->setAttribute('name', 'xsrf');
        $obj->setAttribute('value', self::getToken());

        return $obj;
    }

    /**
     * This is the nonce used to stop CSRF/XSRF attacks. It's stored in the user session.
     *
     * @return string
     */
    public static function getToken()
    {
        $token = self::getSessionToken();
        if (null === $token) {
            $nonce = self::generateNonce();
            self::setSessionToken($nonce);

        // Handle old tokens (< 2.6.0)
        } elseif (is_array($token)) {
            $nonce = key($token);
            self::setSessionToken($nonce);

        // New style tokens
        } else {
            $nonce = $token;
        }

        return $nonce;
    }

    /**
     * This will determine if a token is valid.
     *
     * @param string $xsrf
     *                     The token to validate
     *
     * @return bool
     */
    public static function validateToken($xsrf)
    {
        $token = self::getSessionToken();

        return $token === $xsrf;
    }

    /**
     * This will validate a request has a good token.
     *
     * @throws SymphonyErrorPage
     *
     * @param bool $silent
     *                     If true, this function will return false if the request fails,
     *                     otherwise it will throw an Exception. By default this function
     *                     will thrown an exception if the request is invalid.
     *
     * @return false|void
     */
    public static function validateRequest($silent = false)
    {
        // Only care if we have a POST request.
        if (count($_POST) > 0) {
            if (!self::validateToken($_POST['xsrf'])) {
                // Token was invalid, show an error page.
                if (!$silent) {
                    self::throwXSRFException();
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * The error function that's thrown if the token is invalid.
     *
     * @throws SymphonyErrorPage
     */
    public static function throwXSRFException()
    {
        $msg =
            __('Request was rejected for having an invalid cross-site request forgery token.')
            .'<br/><br/>'.
            __('Please go back and try again.');
        throw new Exceptions\SymphonyErrorPageException($msg, __('Access Denied'), 'generic', [], AbstractPage::HTTP_STATUS_FORBIDDEN);
    }
}
