<?php

declare(strict_types=1);

namespace Stu\Exception;

final class SanityCheckException extends StuException
{
    private ?string $actionIdentifier;
    private ?string $viewIdentifier;

    public function __construct($message = "", ?string $actionIdentifier = null, ?string $viewIdentifier = null)
    {
        $this->message = $message;
        $this->actionIdentifier = $actionIdentifier;
        $this->viewIdentifier = $viewIdentifier;
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
