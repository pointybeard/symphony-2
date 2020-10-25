<?php

//declare(strict_types=1);

namespace Symphony\Symphony;

use Symphony\Symphony\Cryptography\Pbkdf2;

/**
 * Cryptography is a utility class that offers a number of general purpose cryptography-
 * related functions for message digestation as well as (backwards-)compatibility
 * checking. The message digestation algorithms are placed in the subclasses
 * `MD5`, `SHA1` and `PBKDF2`.
 *
 * @since Symphony 2.3.1
 * @see cryptography.MD5
 * @see cryptography.SHA1
 * @see cryptography.PBKDF2
 */
class Cryptography
{
    /**
     * Uses an instance of `PBKDF2` to create a hash. If you require other
     * hashes, see the related functions of the `MD5` or `SHA1` classes.
     *
     * @see cryptography.MD5#hash()
     * @see cryptography.SHA1#hash()
     * @see cryptography.PBKDF2#hash()
     *
     * @param string $input
     *                      the string to be hashed
     *
     * @return string
     *                the hashed string
     */
    public static function hash($input)
    {
        return Pbkdf2::hash($input);
    }

    /**
     * Compares a given hash with a clean text password by figuring out the
     * algorithm that has been used and then calling the appropriate sub-class.
     *
     * @see cryptography.MD5#compare()
     * @see cryptography.SHA1#compare()
     * @see cryptography.PBKDF2#compare()
     *
     * @param string $input
     *                       the cleartext password
     * @param string $hash
     *                       the hash the password should be checked against
     * @param bool   $isHash
     *
     * @return bool
     *              the result of the comparison
     */
    public static function compare($input, $hash, $isHash = false)
    {
        $version = substr($hash, 0, 8);

        if (true === $isHash) {
            return $input == $hash;

        } elseif ('PBKDF2v1' == $version) { // salted PBKDF2
            return Pbkdf2::compare($input, $hash);

        } else { // the hash provided doesn't make any sense
            return false;
        }
    }

    /**
     * Checks if provided hash has been computed by most recent algorithm
     * returns true if otherwise.
     *
     * @param string $hash
     *                     the hash to be checked
     *
     * @return bool
     *              whether the hash should be re-computed
     */
    public static function requiresMigration($hash)
    {
        $version = substr($hash, 0, 8);

        if (Pbkdf2::PREFIX == $version) { // salted PBKDF2, let the responsible class decide
            return Pbkdf2::requiresMigration($hash);
        } else { // everything else
            return true;
        }
    }

    /**
     * Generates a salt to be used in message digestation.
     *
     * @param int $length
     *                    the length of the salt
     *
     * @return string
     *                a hexadecimal string
     */
    public static function generateSalt($length)
    {
        mt_srand(intval(microtime(true) * 100000 + memory_get_usage(true)));

        return substr(sha1(uniqid(mt_rand(), true)), 0, $length);
    }
}
