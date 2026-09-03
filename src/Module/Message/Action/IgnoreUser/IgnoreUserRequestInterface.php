<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\IgnoreUser;

interface IgnoreUserRequestInterface
{
    public function getRecipientId(): int;
}
