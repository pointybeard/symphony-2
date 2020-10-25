<?php

//declare(strict_types=1);

namespace Symphony\Symphony\Exceptions;

/**
 * The validation exception to be thrown by all email gateways.
 * This exception is thrown if data does not pass validation.
 */
class EmailValidationException extends EmailGatewayException
{
}
