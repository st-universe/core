<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register\Exception;

use Stu\Component\ErrorHandling\ErrorCodeEnum;

final class MobileNumberInvalidException extends RegistrationException
{
    protected $code = ErrorCodeEnum::SMS_VERIFICATION_CODE_INVALID;

    protected $message = 'The provided mobile number is not valid';
}
