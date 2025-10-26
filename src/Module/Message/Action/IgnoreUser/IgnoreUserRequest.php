<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\IgnoreUser;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class IgnoreUserRequest implements IgnoreUserRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getRecipientId(): int
    {
        return $this->parameter('recid')->int()->required();
    }
}
