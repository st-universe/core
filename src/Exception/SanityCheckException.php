<?php

declare(strict_types=1);

namespace Stu\Exception;

final class SanityCheckException extends StuException
{
    private ?string $action;
    private ?string $view;

    public function __construct($message = "", ?string $action = null, ?string $view = null)
    {
        $this->message = $message;
        $this->action = $action;
        $this->view = $view;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function getView(): ?string
    {
        return $this->view;
    }
}
