<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeUserName;

interface ChangeUserNameRequestInterface
{
    public function getName(): string;
}
