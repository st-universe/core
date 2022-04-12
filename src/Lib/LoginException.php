<?php

declare(strict_types=1);

namespace Stu\Lib;

use Exception;

final class LoginException extends Exception
{
    private ?string $details;

    public function __construct(string $message = '', string $details = null)
    {
        $this->message = $message;
        $this->details = $details;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }
}
