<?php

namespace Symphony\Symphony\Exceptions;

/**
 * The DatabaseException class extends a normal Exception to add in
 * debugging information when a SQL query fails such as the internal
 * database error code and message in additional to the usual
 * Exception information. It allows a DatabaseException to contain a human
 * readable error, as well more technical information for debugging.
 */
class DatabaseException extends SymphonyException
{
    /**
     * An associative array with three keys, 'query', 'msg' and 'num'.
     *
     * @var array
     */
    private $error = [];

    /**
     * Constructor takes a message and an associative array to set to
     * `$error`. The message is passed to the default Exception constructor.
     */
    public function __construct($message, array $error = null)
    {
        parent::__construct($message);
        $this->error = $error;
    }

    /**
     * Accessor function for the original query that caused this Exception.
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->error['query'];
    }

    /**
     * Accessor function for the Database error code for this type of error.
     *
     * @return int
     */
    public function getDatabaseErrorCode()
    {
        return $this->error['num'];
    }

    /**
     * Accessor function for the Database message from this Exception.
     *
     * @return string
     */
    public function getDatabaseErrorMessage()
    {
        return $this->error['msg'];
    }
}
