<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\MovePm;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class MovePmRequest implements MovePmRequestInterface
{
    use CustomControllerHelperTrait;

    public function getCategoryId(): int
    {
        return $this->queryParameter('pmcat')->int()->required();
    }

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