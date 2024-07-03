<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Login;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class LoginRequest implements LoginRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getLoginName(): string
    {
        return $this->queryParameter('login')->string()->defaultsToIfEmpty('');
    }

    #[Override]
    public function getPassword(): string
    {
        return $this->queryParameter('pass')->string()->defaultsToIfEmpty('');
    }
}
