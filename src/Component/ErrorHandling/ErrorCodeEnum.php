<?php

declare(strict_types=1);

namespace Stu\Component\ErrorHandling;

final class ErrorCodeEnum
{
    public const LOGIN_NAME_INVALID = 100001;
    public const EMAIL_ADDRESS_INVALID = 100002;
    public const REGISTRATION_DUPLICATE = 100003;
    public const INVALID_FACTION = 100004;
    public const AUTHENTICATION_FAILED = 100005;
    public const NOT_FOUND = 100006;
    public const REGISTRATION_NOT_PERMITTED = 100007;
    public const REGISTRATION_TOKEN_INVALID = 100008;
    public const SMS_VERIFICATION_CODE_INVALID = 100009;
}
