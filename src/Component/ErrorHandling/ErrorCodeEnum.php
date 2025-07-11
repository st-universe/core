<?php

declare(strict_types=1);

namespace Stu\Component\ErrorHandling;

enum ErrorCodeEnum: int
{
    case LOGIN_NAME_INVALID = 100001;
    case EMAIL_ADDRESS_INVALID = 100002;
    case REGISTRATION_DUPLICATE = 100003;
    case SMS_VERIFICATION_CODE_INVALID = 100009;

    public function getDescription(): string
    {
        return match ($this) {
            self::LOGIN_NAME_INVALID => 'The provided login name is invalid (invalid characters or invalid length)',
            self::EMAIL_ADDRESS_INVALID => 'The provided email address is not valid',
            self::REGISTRATION_DUPLICATE => 'The provided email address or username are already registered',
            self::SMS_VERIFICATION_CODE_INVALID => 'The provided mobile number is not valid'
        };
    }
}
