<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateRelation;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreateRelationRequest implements CreateRelationRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getCounterpartId(): int
    {
        return $this->parameter('oid')->int()->required();
    }

    #[\Override]
    public function getRelationType(): int
    {
        return $this->parameter('type')->int()->required();
    }
}
