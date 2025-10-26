<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangePassword;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ChangePasswordRequest implements ChangePasswordRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getCurrentPassword(): string
    {
        return $this->parameter('oldpass')->string()->defaultsToIfEmpty('');
    }

    #[\Override]
    public function getNewPassword(): string
    {
        return $this->parameter('pass')->string()->defaultsToIfEmpty('');
    }

    #[\Override]
    public function getNewPasswordReEntered(): string
    {
        return $this->parameter('pass2')->string()->defaultsToIfEmpty('');
    }
}
