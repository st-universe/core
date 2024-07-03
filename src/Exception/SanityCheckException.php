<?php

declare(strict_types=1);

namespace Stu\Exception;

final class SanityCheckException extends StuException
{
    public function __construct($message = "", private ?string $actionIdentifier = null, private ?string $viewIdentifier = null)
    {
        $this->message = $message;
    }

    public function getActionIdentifier(): ?string
    {
        return $this->actionIdentifier;
    }

    public function getViewIdentifier(): ?string
    {
        return $this->viewIdentifier;
    }
}
