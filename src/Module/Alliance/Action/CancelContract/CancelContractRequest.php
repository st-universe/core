<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CancelContract;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class CancelContractRequest implements CancelContractRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getRelationId(): int
    {
        return $this->parameter('al')->int()->required();
    }
}
