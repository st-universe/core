<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeColonyMessage;

interface ChangeColonyMessageRequestInterface
{
    public function getColonyMessage(): string;
}
