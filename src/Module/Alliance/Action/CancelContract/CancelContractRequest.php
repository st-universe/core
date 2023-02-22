<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CancelContract;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CancelContractRequest implements CancelContractRequestInterface
{
    use CustomControllerHelperTrait;

    public function getRelationId(): int
    {
        return $this->queryParameter('al')->int()->required();
    }
}
