<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\DeletionConfirmation;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeletionConfirmationRequest implements DeletionConfirmationRequestInterface
{
    use CustomControllerHelperTrait;

    public function getToken(): string
    {
        return $this->queryParameter('token')->string()->defaultsToIfEmpty('');
    }
}
