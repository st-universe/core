<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register\Exception;

use Stu\Component\ErrorHandling\ErrorCodeEnum;

final class LoginNameInvalidException extends RegistrationException
{
    /** @var int */
    protected $code = ErrorCodeEnum::LOGIN_NAME_INVALID;

    /** @var string */
    protected $message = 'The provided login name is invalid (invalid characters or invalid length)';
}
