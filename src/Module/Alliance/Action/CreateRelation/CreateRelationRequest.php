<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateRelation;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreateRelationRequest implements CreateRelationRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getCounterpartId(): int
    {
        return $this->queryParameter('oid')->int()->required();
    }

    #[Override]
    public function getRelationType(): int
    {
        return $this->queryParameter('type')->int()->required();
    }
}
