<?php

namespace Stu\Module\Index\Action\Login;

interface LoginRequestInterface
{
    public function getLoginName(): string;

    public function getPassword(): string;
}
