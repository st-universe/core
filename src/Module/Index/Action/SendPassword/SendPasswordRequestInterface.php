<?php

namespace Stu\Module\Index\Action\SendPassword;

interface SendPasswordRequestInterface
{
    public function getEmailAddress(): string;
}
