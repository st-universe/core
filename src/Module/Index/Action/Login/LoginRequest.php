<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Login;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class LoginRequest implements LoginRequestInterface
{
    use CustomControllerHelperTrait;

    public function getLoginName(): string
    {
        return $this->queryParameter('login')->string()->defaultsToIfEmpty('');
    }

    public function getPassword(): string
    {
        return $this->queryParameter('pass')->string()->defaultsToIfEmpty('');
    }

    public function getSmsVerificationCode(): ?string
    {
        return $this->queryParameter('smscode')->string()->defaultsToIfEmpty(null);
    }
}
