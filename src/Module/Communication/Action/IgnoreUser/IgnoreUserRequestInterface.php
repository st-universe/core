<?php

namespace Stu\Module\Communication\Action\IgnoreUser;

interface IgnoreUserRequestInterface
{
    public function getRecipientId(): int;
}