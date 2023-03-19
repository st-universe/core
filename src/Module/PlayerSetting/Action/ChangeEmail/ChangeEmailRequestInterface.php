<?php

namespace Stu\Module\PlayerSetting\Action\ChangeEmail;

interface ChangeEmailRequestInterface
{
    public function getEmailAddress(): string;
}
