<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register\Exception;

use Stu\Component\ErrorHandling\ErrorCodeEnum;

final class PlayerDuplicateException extends RegistrationException
{
    protected $code = ErrorCodeEnum::REGISTRATION_DUPLICATE;

    protected $message = 'The provided email address or username are already registered';
}