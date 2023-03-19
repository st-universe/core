<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowResetPassword;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowResetPasswordRequest implements ShowResetPasswordRequestInterface
{
    use CustomControllerHelperTrait;

    public function getToken(): string
    {
        return $this->queryParameter('TOKEN')->string()->defaultsToIfEmpty('');
    }
}
