<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\SendPassword;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class SendPasswordRequest implements SendPasswordRequestInterface
{
    use CustomControllerHelperTrait;

    public function getEmailAddress(): string
    {
        return $this->queryParameter('emailaddress')->string()->defaultsToIfEmpty('');
    }
}
