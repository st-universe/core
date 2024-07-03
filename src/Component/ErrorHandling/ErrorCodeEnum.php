<?php

declare(strict_types=1);

namespace Stu\Component\ErrorHandling;

final class ErrorCodeEnum
{
    /**
     * @var int
     */
    public const int LOGIN_NAME_INVALID = 100001;

    /**
     * @var int
     */
    public const int EMAIL_ADDRESS_INVALID = 100002;

    /**
     * @var int
     */
    public const int REGISTRATION_DUPLICATE = 100003;

    /**
     * @var int
     */
    public const int INVALID_FACTION = 100004;

    /**
     * @var int
     */
    public const int AUTHENTICATION_FAILED = 100005;

    /**
     * @var int
     */
    public const int NOT_FOUND = 100006;

    /**
     * @var int
     */
    public const int REGISTRATION_NOT_PERMITTED = 100007;

    /**
     * @var int
     */
    public const int REGISTRATION_TOKEN_INVALID = 100008;

    /**
     * @var int
     */
    public const int SMS_VERIFICATION_CODE_INVALID = 100009;
}
