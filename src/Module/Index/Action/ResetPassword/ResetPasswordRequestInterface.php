<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\ResetPassword;

interface ResetPasswordRequestInterface
{
    public function getToken(): string;
}
