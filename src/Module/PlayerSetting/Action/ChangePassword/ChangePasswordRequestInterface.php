<?php

namespace Stu\Module\PlayerSetting\Action\ChangePassword;

interface ChangePasswordRequestInterface
{
    public function getCurrentPassword(): string;

    public function getNewPassword(): string;

    public function getNewPasswordReEntered(): string;
}
