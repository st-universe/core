<?php

declare(strict_types=1);

namespace Stu\Lib;

use Stu\Exception\StuException;

final class UserLockedException extends StuException
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
