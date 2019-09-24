<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register\Exception;

use Stu\Component\ErrorHandling\ErrorCodeEnum;

final class EmailAddressInvalidException extends RegistrationException
{
    protected $code = ErrorCodeEnum::EMAIL_ADDRESS_INVALID;

    protected $message = 'The provided email address is not valid';
}