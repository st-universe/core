<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Login;

interface LoginRequestInterface
{
    public function getLoginName(): string;

    public function getPassword(): string;
}
