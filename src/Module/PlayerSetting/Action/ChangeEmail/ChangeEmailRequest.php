<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeEmail;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ChangeEmailRequest implements ChangeEmailRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getEmailAddress(): string
    {
        return $this->queryParameter('email')->string()->defaultsToIfEmpty('');
    }
}
