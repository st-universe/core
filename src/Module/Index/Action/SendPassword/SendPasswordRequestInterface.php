<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\SendPassword;

interface SendPasswordRequestInterface
{
    public function getEmailAddress(): string;
}
