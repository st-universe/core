<?php

declare(strict_types=1);

namespace Stu\Component\ErrorHandling;

final class ErrorCodeEnum
{
    /**
     * @var int
     */
    public const LOGIN_NAME_INVALID = 100001;

    /**
     * @var int
     */
    public const EMAIL_ADDRESS_INVALID = 100002;

    /**
     * @var int
     */
    public const REGISTRATION_DUPLICATE = 100003;

    /**
     * @var int
     */
    public const INVALID_FACTION = 100004;

    /**
     * @var int
     */
    public const AUTHENTICATION_FAILED = 100005;

    /**
     * @var int
     */
    public const NOT_FOUND = 100006;

    /**
     * @var int
     */
    public const REGISTRATION_NOT_PERMITTED = 100007;

    /**
     * @var int
     */
    public const REGISTRATION_TOKEN_INVALID = 100008;

    /**
     * @var int
     */
    public const SMS_VERIFICATION_CODE_INVALID = 100009;
}
