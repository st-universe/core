<?php

namespace Stu\Module\Message\Action\IgnoreUser;

interface IgnoreUserRequestInterface
{
    public function getRecipientId(): int;
}
