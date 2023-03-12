<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register\Exception;

use Stu\Component\ErrorHandling\ErrorCodeEnum;

final class EmailAddressInvalidException extends RegistrationException
{
    /** @var int */
    protected $code = ErrorCodeEnum::EMAIL_ADDRESS_INVALID;

    /** @var string */
    protected $message = 'The provided email address is not valid';
}
