<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\MovePm;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class MovePmRequest implements MovePmRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getPmId(): int
    {
        return $this->queryParameter('move_pm')->int()->required();
    }

    #[Override]
    public function getDestinationCategoryId(): int
    {
        return $this->queryParameter(
            sprintf('movecat_%d', $this->getPmId())
        )->int()->required();
    }
}
