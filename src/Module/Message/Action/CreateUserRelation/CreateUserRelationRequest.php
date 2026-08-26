<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\CreateUserRelation;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreateUserRelationRequest implements CreateUserRelationRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getTargetId(): int
    {
        return $this->parameter('relation_target')->int()->required();
    }

    #[\Override]
    public function getTargetType(): int
    {
        return $this->parameter('relation_target_type')->int()->required();
    }

    #[\Override]
    public function getRelationType(): int
    {
        return $this->parameter('relation_type')->int()->required();
    }
}
