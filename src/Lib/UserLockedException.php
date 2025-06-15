<?php

declare(strict_types=1);

namespace Stu\Lib;

use Stu\Module\Control\Router\FallbackRouteException;

final class UserLockedException extends FallbackRouteException
{
    public function __construct(string $message, private string $details)
    {
        $this->message = $message;
    }

    public function getDetails(): string
    {
        return $this->details;
    }
}
