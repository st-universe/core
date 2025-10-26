<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\ResetPassword;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getToken(): string
    {
        return $this->parameter('TOKEN')->string()->defaultsToIfEmpty('');
    }
}
