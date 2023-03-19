<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangePassword;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ChangePasswordRequest implements ChangePasswordRequestInterface
{
    use CustomControllerHelperTrait;

    public function getCurrentPassword(): string
    {
        return $this->queryParameter('oldpass')->string()->defaultsToIfEmpty('');
    }

    public function getNewPassword(): string
    {
        return $this->queryParameter('pass')->string()->defaultsToIfEmpty('');
    }

    public function getNewPasswordReEntered(): string
    {
        return $this->queryParameter('pass2')->string()->defaultsToIfEmpty('');
    }
}
