<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\DeletionConfirmation;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeletionConfirmationRequest implements DeletionConfirmationRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getToken(): string
    {
        return $this->parameter('token')->string()->defaultsToIfEmpty('');
    }
}
