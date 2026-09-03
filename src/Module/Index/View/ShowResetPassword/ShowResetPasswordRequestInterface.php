<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowResetPassword;

interface ShowResetPasswordRequestInterface
{
    public function getToken(): string;
}
