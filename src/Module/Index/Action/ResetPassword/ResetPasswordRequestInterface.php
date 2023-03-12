<?php

namespace Stu\Module\Index\Action\ResetPassword;

interface ResetPasswordRequestInterface
{
    public function getToken(): string;
}