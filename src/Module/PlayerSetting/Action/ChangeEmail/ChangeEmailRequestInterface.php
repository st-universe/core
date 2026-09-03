<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeEmail;

interface ChangeEmailRequestInterface
{
    public function getEmailAddress(): string;
}
