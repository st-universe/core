<?php

namespace Stu\Module\Index\View\ShowResetPassword;

interface ShowResetPasswordRequestInterface
{
    public function getToken(): string;
}