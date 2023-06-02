<?php

declare(strict_types=1);

namespace Stu\Lib;

use Stu\Exception\StuException;

final class UserLockedException extends StuException
{
    private string $details;

    public function __construct(string $message, string $details)
    {
        $this->message = $message;
        $this->details = $details;
    }

    public function getDetails(): string
    {
        return $this->details;
    }
}
