<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register\Exception;

use Exception;
use Stu\Component\ErrorHandling\ErrorCodeEnum;

class RegistrationException extends Exception
{
    public function __construct(protected ErrorCodeEnum $errorType)
    {
        $this->message = $errorType->getDescription();
    }
}
