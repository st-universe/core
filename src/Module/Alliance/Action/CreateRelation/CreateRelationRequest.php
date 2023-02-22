<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateRelation;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreateRelationRequest implements CreateRelationRequestInterface
{
    use CustomControllerHelperTrait;

    public function getCounterpartId(): int
    {
        return $this->queryParameter('oid')->int()->required();
    }

    public function getRelationType(): int
    {
        return $this->queryParameter('type')->int()->required();
    }
}
