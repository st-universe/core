<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\MovePm;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class MovePmRequest implements MovePmRequestInterface
{
    use CustomControllerHelperTrait;

    public function getPmId(): int
    {
        return $this->queryParameter('move_pm')->int()->required();
    }

    public function getDestinationCategoryId(): int
    {
        return $this->queryParameter(
            sprintf('movecat_%d', $this->getPmId())
        )->int()->required();
    }
}
