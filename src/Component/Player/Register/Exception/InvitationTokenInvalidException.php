<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register\Exception;

use Stu\Component\ErrorHandling\ErrorCodeEnum;

final class InvitationTokenInvalidException extends RegistrationException
{
    /** @var int */
    protected $code = ErrorCodeEnum::REGISTRATION_DUPLICATE;

    /** @var string */
    protected $message = 'The provided invitation token is invalid';
}
