<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\SendPassword;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class SendPasswordRequest implements SendPasswordRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getEmailAddress(): string
    {
        return $this->parameter('emailaddress')->string()->defaultsToIfEmpty('');
    }
}
