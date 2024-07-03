<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\DeletionConfirmation;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeletionConfirmationRequest implements DeletionConfirmationRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getToken(): string
    {
        return $this->queryParameter('token')->string()->defaultsToIfEmpty('');
    }
}
